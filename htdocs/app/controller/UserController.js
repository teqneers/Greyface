Ext.define("Greyface.controller.UserController", {
    extend: "Ext.app.Controller",
    refs: [
        { ref: "userAdminGrid", selector: "panel[actionId=userAdminPanel]" },
        { ref: "userAdminGridPagingToolbar", selector: "pagingtoolbar[actionId=userAdminPanelPagingToolbar]" },
        { ref: "userAliasGrid", selector: "panel[actionId=userAliasPanel]" },
        { ref: "userAliasGridPagingToolbar", selector: "pagingtoolbar[actionId=userAliasPanelPagingToolbar]" },
        { ref: "userFilterTextfield", selector: "textfield[actionId=userAdminToolbarSearchForUser]" },
        { ref: "userFilterCancelButton", selector: "button[actionId=userAdminToolbarCancelFilter]" },
        { ref: "aliasFilterTextfield", selector: "textfield[actionId=userAliasToolbarSearchForAlias]" },
        { ref: "aliasFilterCancelButton", selector: "button[actionId=userAliasToolbarCancelFilter]" },
        { ref: "aliasFilterCombo", selector: "combo[actionId=userAliasToolbarFilterBy]" }
    ],
    views: [
        "Greyface.view.user.admin.GridPanel",
        "Greyface.view.user.admin.Toolbar",
        "Greyface.view.user.alias.GridPanel",
        "Greyface.view.user.alias.Toolbar"
    ],
    stores:[
        "Greyface.store.UserAdminStore",
        "Greyface.store.UserAliasStore",
        "Greyface.store.UserAliasFilterStore"
    ],
    init: function () {
        this.control({
            // wire up the stores to the datagrid in view
            "panel[actionId=mainScreen]": {
                beforerender: function() {
                    this.wireupUserAdminStore(),
                    this.wireupUserAliasStore()
                }
            },

            // Add User
            "button[actionId=userAdminAddUser]": {
                click: function(){
                    this.openAddUserWindow();
                }
            },
            // User Filter
            "textfield[actionId=userAdminToolbarSearchForUser]": {
                change: this.initUserFilter,
                specialkey: function(textfield, eventObjet) {
                    if(eventObjet.getKey() == eventObjet.ESC) {
                        textfield.setValue("");
                    }
                }
            },
            "button[actionId=userAdminToolbarCancelFilter]": {
                click: function() {this.getUserFilterTextfield().setValue("")}
            },


            // Add Alias
            "button[actionId=userAliasToolbarAddAlias]": {
                click: function(){
                    this.openAddAliasWindow();
                }
            },
            // Alias Filter
            "textfield[actionId=userAliasToolbarSearchForAlias]": {
                change: this.initAliasFilter,
                specialkey: function(textfield, eventObjet) {
                    if(eventObjet.getKey() == eventObjet.ESC) {
                        textfield.setValue("");
                    }
                }
            },
            "button[actionId=userAliasToolbarCancelFilter]": {
                click: function() {this.getAliasFilterTextfield().setValue("")}
            },

            // Filter aliases after user
            "combobox[actionId=userAliasToolbarFilterBy]": {
                change: function(box, newValue, oldValue, eOpts){
                    var store = Ext.getStore("Greyface.store.UserAliasStore");
                    if(box.getValue() == "show_all") {
                        store.filters.removeAtKey("user_id"); //@WORKAROUND because a bug in ExtJS! (store.removeFilter("ID") //
                    } else {
                        store.addFilter([{id:"user_id", property:"user_id", value:box.getValue()}], false);
                    }
                    store.load();
                }
            }
        });
    },

    wireupUserAdminStore: function () {
        var store = Ext.getStore("Greyface.store.UserAdminStore");
        store.load();
        this.getUserAdminGrid().reconfigure(store)
        this.getUserAdminGridPagingToolbar().bindStore(store);
    },
    wireupUserAliasStore: function () {
        var store = Ext.getStore("Greyface.store.UserAliasStore");
        store.load();
        this.getUserAliasGrid().reconfigure(store)
        this.getUserAliasGridPagingToolbar().bindStore(store);

        // wires up the store for the combobox which shows all users, after which the grid can be filtered.
        var userAliasFilterStore = Ext.getStore("Greyface.store.UserAliasFilterStore");
        userAliasFilterStore.load();
        this.getAliasFilterCombo().bindStore(userAliasFilterStore);
    },


    // Filtering User
    initUserFilter: function() {
        var task = this.createUserFilterTask();
        task.delay(300)
    },
    toggleUserFilter: function() {
        var store = Ext.getStore("Greyface.store.UserAdminStore");
        var filterValue= this.getUserFilterTextfield().getValue();
        if(filterValue == ""){
            store.clearFilter();
            this.getUserFilterCancelButton().hide();
        } else {
            this.getUserFilterCancelButton().show();
            store.clearFilter(true);
            store.filter([
                {property:"username", value:"%"+filterValue+"%"},
                {property:"email", value:"%"+filterValue+"%"}
            ]);
        }
    },
    createUserFilterTask: function() {
        if(!Greyface.tools.Registry.exists(("UserFilterTextfield"))) {
            var task = new Ext.util.DelayedTask(this.toggleUserFilter, this);
            Greyface.tools.Registry.set("UserFilterTextfield", task);
        } else {
            var task = Greyface.tools.Registry.get("UserFilterTextfield")
        }
        return task;
    },


    // Filtering Alias
    initAliasFilter: function() {
        var task = this.createAliasFilterTask();
        task.delay(300)
    },
    toggleAliasFilter: function() {
        var store = Ext.getStore("Greyface.store.UserAliasStore");
        var filterValue= this.getAliasFilterTextfield().getValue();
        if(filterValue == ""){
            store.clearFilter();
            this.getAliasFilterCancelButton().hide();
        } else {
            this.getAliasFilterCancelButton().show();
            store.clearFilter(true);
            store.filter([
                {property:"username", value:"%"+filterValue+"%"},
                {property:"email", value:"%"+filterValue+"%"}
            ]);
        }
    },
    createAliasFilterTask: function() {
        if(!Greyface.tools.Registry.exists(("AliasFilterTextfield"))) {
            var task = new Ext.util.DelayedTask(this.toggleAliasFilter, this);
            Greyface.tools.Registry.set("AliasFilterTextfield", task);
        } else {
            var task = Greyface.tools.Registry.get("AliasFilterTextfield")
        }
        return task;
    },

    // Add user
    openAddUserWindow: function() {
        Ext.create("Greyface.view.user.admin.AddUserWindow", {
            callbackAddUser:this.addUser
        }).show();
    },
    addUser: function(username, email, password, isAdmin, randomizePassword, sendEmail) {
        Ext.Ajax.request({
            url: "api/CRUDRouter.php?action=addUser",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse.data[0])
            },
            failure: function(response, opts) {
                // nothing
            },
            method: "GET",
            params: {
                store:"userAdminStore",
                username:username,
                email:email,
                password:password,
                isAdmin:isAdmin,
                randomizePassword:randomizePassword,
                sendEmail:sendEmail
            }
        });

        var store = Ext.getStore("Greyface.store.UserAdminStore");
        store.reload();
    },

    // Add alias
    openAddAliasWindow: function() {
        Ext.create("Greyface.view.user.alias.AddAliasWindow", {
            callbackAddAlias:this.addAlias
        }).show();
    },
    addAlias: function(username, alias) {
        Ext.Ajax.request({
            url: "api/CRUDRouter.php?action=addAlias",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse.data[0])
            },
            failure: function(response, opts) {
                // nothing
            },
            method: "GET",
            params: {
                store:"userAliasStore",
                username:username,
                alias:alias
            }
        });

        var store = Ext.getStore("Greyface.store.UserAliasStore");
        store.reload();
    }
});