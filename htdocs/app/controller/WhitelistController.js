Ext.define("Greyface.controller.WhitelistController", {
    extend: "Ext.app.Controller",
    refs: [
        { ref: "emailGrid", selector: "panel[actionId=whitelistEmailPanel]" },
        { ref: "emailGridPagingToolbar", selector: "pagingtoolbar[actionId=whitelistEmailPanelPagingToolbar]" },
        { ref: "domainGrid", selector: "panel[actionId=whitelistDomainPanel]" },
        { ref: "domainGridPagingToolbar", selector: "pagingtoolbar[actionId=whitelistDomainPanelPagingToolbar]" },
        { ref: "eMailFilterTextfield", selector: "textfield[actionId=whitelistToolbarSearchForEmail]" },
        { ref: "eMailFilterCancelButton", selector: "button[actionId=whitelistEmailToolbarCancelFilter]" },
        { ref: "domainFilterTextfield", selector: "textfield[actionId=whitelistToolbarSearchForDomain]" },
        { ref: "domainFilterCancelButton", selector: "button[actionId=whitelistDomainToolbarCancelFilter]" }
    ],
    views: [
        "Greyface.view.whitelist.email.GridPanel",
        "Greyface.view.whitelist.email.Toolbar",
        "Greyface.view.whitelist.domain.GridPanel",
        "Greyface.view.whitelist.domain.Toolbar"
    ],
    stores:[
        "Greyface.store.WhitelistEmailStore",
        "Greyface.store.WhitelistDomainStore"
    ],
    init: function () {
        this.control({
            // wire up the stores to the datagrid in view
            "panel[actionId=mainScreen]": {
                beforerender: function() {
                    this.wireupEmailStore(),
                    this.wireupDomainStore()
                }
            },


            // Add Email
            "button[actionId=whitelistToolbarAddEmail]": {
                click: function(){
                    this.openAddEmailToWhitelist();
                }
            },
            // Email Filter
            "textfield[actionId=whitelistToolbarSearchForEmail]": {
                change: this.initEmailFilter,
                specialkey: function(textfield, eventObjet) {
                    if(eventObjet.getKey() == eventObjet.ESC) {
                        textfield.setValue("");
                    }
                }
            },
            "button[actionId=whitelistEmailToolbarCancelFilter]": {
                click: function() {this.getEMailFilterTextfield().setValue("")}
            },


            // Add Domain
            "button[actionId=whitelistToolbarAddDomain]": {
                click: function(){
                    this.openAddDomainToWhitelist();
                }
            },
            // Domain Filter
            "textfield[actionId=whitelistToolbarSearchForDomain]": {
                change: this.initDomainFilter,
                specialkey: function(textfield, eventObjet) {
                    if(eventObjet.getKey() == eventObjet.ESC) {
                        textfield.setValue("");
                    }
                }
            },
            "button[actionId=whitelistDomainToolbarCancelFilter]": {
                click: function() {this.getDomainFilterTextfield().setValue("")}
            }
        });
    },

    wireupEmailStore: function () {
        var store = Ext.getStore("Greyface.store.WhitelistEmailStore");
        store.load();
        this.getEmailGrid().reconfigure(store)
        this.getEmailGridPagingToolbar().bindStore(store);
    },
    wireupDomainStore: function () {
        var store = Ext.getStore("Greyface.store.WhitelistDomainStore");
        store.load();
        this.getDomainGrid().reconfigure(store)
        this.getDomainGridPagingToolbar().bindStore(store);
    },



    // Filtering Email
    initEmailFilter: function() {
        var task = this.createEmailFilterTask();
        task.delay(300)
    },
    toggleEmailFilter: function() {
        var store = Ext.getStore("Greyface.store.WhitelistEmailStore");
        var filterValue= this.getEMailFilterTextfield().getValue();
        if(filterValue == ""){
            store.clearFilter();
            this.getEMailFilterCancelButton().hide();
        } else {
            this.getEMailFilterCancelButton().show();
            store.clearFilter(true);
            store.filter([
                {property:"email", value:"%"+filterValue+"%"}
            ]);
        }
    },
    createEmailFilterTask: function() {
        if(!Greyface.tools.Registry.exists(("WhitelistEmailFilterTextfield"))) {
            var task = new Ext.util.DelayedTask(this.toggleEmailFilter, this);
            Greyface.tools.Registry.set("WhitelistEmailFilterTextfield", task);
        } else {
            var task = Greyface.tools.Registry.get("WhitelistEmailFilterTextfield")
        }
        return task;
    },

    // Filtering Domain...
    initDomainFilter: function() {
        var task = this.createDomainFilterTask();
        task.delay(300)
    },
    toggleDomainFilter: function() {
        var store = Ext.getStore("Greyface.store.WhitelistDomainStore");
        var filterValue= this.getDomainFilterTextfield().getValue();
        if(filterValue == ""){
            store.clearFilter();
            this.getDomainFilterCancelButton().hide();
        } else {
            this.getDomainFilterCancelButton().show();
            store.clearFilter(true);
            store.filter([
                {property:"domain", value:"%"+filterValue+"%"}
            ]);
        }
    },
    createDomainFilterTask: function() {
        if(!Greyface.tools.Registry.exists(("WhitelistDomainFilterTextfield"))) {
            var task = new Ext.util.DelayedTask(this.toggleDomainFilter, this);
            Greyface.tools.Registry.set("WhitelistDomainFilterTextfield", task);
        } else {
            var task = Greyface.tools.Registry.get("WhitelistDomainFilterTextfield")
        }
        return task;
    },

    // Add Items
    openAddEmailToWhitelist: function() {
        Ext.create("Greyface.view.whitelist.email.AddEmailWindow", {
            callbackDeleteEntries:this.addEmail
        }).show();
    },
    addEmail: function(email) {
        Ext.Ajax.request({
            url: "api/CRUDRouter.php?action=addEmail",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse.data[0])
            },
            failure: function(response, opts) {
                // nothing
            },
            method: "POST",
            params: {
                store:"whitelistEmailStore",
                email:email
            }
        });

        var store = Ext.getStore("Greyface.store.WhitelistEmailStore");
        store.reload();
    },

    openAddDomainToWhitelist: function() {
        Ext.create("Greyface.view.whitelist.domain.AddDomainWindow", {
            callbackDeleteEntries:this.addDomain
        }).show();
    },
    addDomain: function(domain) {
        Ext.Ajax.request({
            url: "api/CRUDRouter.php?action=addDomain",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse.data[0])
            },
            failure: function(response, opts) {
                // nothing
            },
            method: "POST",
            params: {
                store:"whitelistDomainStore",
                domain:domain
            }
        });

        var store = Ext.getStore("Greyface.store.WhitelistDomainStore");
        store.reload();
    }
});