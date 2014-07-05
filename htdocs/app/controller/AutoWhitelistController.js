Ext.define("Greyface.controller.AutoWhitelistController", {
    extend: "Ext.app.Controller",
    refs: [
        { ref: "emailGrid", selector: "panel[actionId=autoWhitelistEmailPanel]" },
        { ref: "emailGridPagingToolbar", selector: "pagingtoolbar[actionId=autoWhitelistEmailPanelPagingToolbar]" },
        { ref: "domainGrid", selector: "panel[actionId=autoWhitelistDomainPanel]" },
        { ref: "domainGridPagingToolbar", selector: "pagingtoolbar[actionId=autoWhitelistDomainPanelPagingToolbar]" },
        { ref: "eMailFilterTextfield", selector: "textfield[actionId=autoWhitelistToolbarSearchForEmail]" },
        { ref: "eMailFilterCancelButton", selector: "button[actionId=autoWhitelistEmailToolbarCancelFilter]" },
        { ref: "domainFilterTextfield", selector: "textfield[actionId=autoWhitelistToolbarSearchForDomain]" },
        { ref: "domainFilterCancelButton", selector: "button[actionId=autoWhitelistDomainToolbarCancelFilter]" }
    ],
    views: [
        "Greyface.view.autowhitelist.email.GridPanel",
        "Greyface.view.autowhitelist.email.Toolbar",
        "Greyface.view.autowhitelist.domain.GridPanel",
        "Greyface.view.autowhitelist.domain.Toolbar"
    ],
    stores:[
        "Greyface.store.AutoWhitelistEmailStore",
        "Greyface.store.AutoWhitelistDomainStore"
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
            "button[actionId=autoWhitelistToolbarAddEmail]": {
                click: function(){
                    this.openAddEmailToAutoWhitelist();
                }
            },
            // Email Filter
            "textfield[actionId=autoWhitelistToolbarSearchForEmail]": {
                change: this.initEmailFilter,
                specialkey: function(textfield, eventObjet) {
                    if(eventObjet.getKey() == eventObjet.ESC) {
                        textfield.setValue("");
                    }
                }
            },
            "button[actionId=autoWhitelistEmailToolbarCancelFilter]": {
                click: function() {this.getEMailFilterTextfield().setValue("")}
            },

            // Add Domain
            "button[actionId=autoWhitelistToolbarAddDomain]": {
                click: function(){
                    this.openAddDomainToAutoWhitelist();
                }
            },
            // Domain Filter
            "textfield[actionId=autoWhitelistToolbarSearchForDomain]": {
                change: this.initDomainFilter,
                specialkey: function(textfield, eventObjet) {
                    if(eventObjet.getKey() == eventObjet.ESC) {
                        textfield.setValue("");
                    }
                }
            },
            "button[actionId=autoWhitelistDomainToolbarCancelFilter]": {
                click: function() {this.getDomainFilterTextfield().setValue("")}
            }

        });
    },


    // Configure Store and connect with gridTable and gridPagingToolbar...
    wireupEmailStore: function () {
        var store = Ext.getStore("Greyface.store.AutoWhitelistEmailStore");
//        store.load(); // Login proccess speed #73 -> improved issue. No need to load all stores on startup!
        this.getEmailGrid().reconfigure(store)
        this.getEmailGridPagingToolbar().bindStore(store);
    },
    wireupDomainStore: function () {
        var store = Ext.getStore("Greyface.store.AutoWhitelistDomainStore");
//        store.load(); // Login proccess speed #73 -> improved issue. No need to load all stores on startup!
        this.getDomainGrid().reconfigure(store)
        this.getDomainGridPagingToolbar().bindStore(store);
    },

    // Filtering Email
    initEmailFilter: function() {
        var task = this.createEmailFilterTask();
        task.delay(300)
    },
    toggleEmailFilter: function() {
        var store = Ext.getStore("Greyface.store.AutoWhitelistEmailStore");
        var filterValue= this.getEMailFilterTextfield().getValue();
        if(filterValue == ""){
            store.clearFilter();
            this.getEMailFilterCancelButton().hide();
        } else {
            this.getEMailFilterCancelButton().show();
            store.clearFilter(true);
            store.filter([
                {property:"sender_name", value:"%"+filterValue+"%"},
                {property:"sender_domain", value:"%"+filterValue+"%"},
                {property:"src", value:"%"+filterValue+"%"},
                {property:"first_seen", value:"%"+filterValue+"%"},
                {property:"last_seen", value:"%"+filterValue+"%"}
            ]);
        }
    },
    createEmailFilterTask: function() {
        if(!Greyface.tools.Registry.exists(("AutoWhitelistEmailFilterTextfield"))) {
            var task = new Ext.util.DelayedTask(this.toggleEmailFilter, this);
            Greyface.tools.Registry.set("AutoWhitelistEmailFilterTextfield", task);
        } else {
            var task = Greyface.tools.Registry.get("AutoWhitelistEmailFilterTextfield")
        }
        return task;
    },

    // Filtering Domain...
    initDomainFilter: function() {
        var task = this.createDomainFilterTask();
        task.delay(300)
    },
    toggleDomainFilter: function() {
        var store = Ext.getStore("Greyface.store.AutoWhitelistDomainStore");
        var filterValue= this.getDomainFilterTextfield().getValue();
        if(filterValue == ""){
            store.clearFilter();
            this.getDomainFilterCancelButton().hide();
        } else {
            this.getDomainFilterCancelButton().show();
            store.clearFilter(true);
            store.filter([
                {property:"sender_domain", value:"%"+filterValue+"%"},
                {property:"src", value:"%"+filterValue+"%"},
                {property:"first_seen", value:"%"+filterValue+"%"},
                {property:"last_seen", value:"%"+filterValue+"%"}
            ]);
        }
    },
    createDomainFilterTask: function() {
        if(!Greyface.tools.Registry.exists(("AutoWhitelistDomainFilterTextfield"))) {
            var task = new Ext.util.DelayedTask(this.toggleDomainFilter, this);
            Greyface.tools.Registry.set("AutoWhitelistDomainFilterTextfield", task);
        } else {
            var task = Greyface.tools.Registry.get("AutoWhitelistDomainFilterTextfield")
        }
        return task;
    },

    // Add Items
    openAddEmailToAutoWhitelist: function() {
        Ext.create("Greyface.view.autowhitelist.email.AddEmailWindow", {
            callbackDeleteEntries:this.addEmail
        }).show();
    },
    addEmail: function(sender, domain, source) {
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
                store:"autoWhitelistEmailStore",
                sender:sender,
                domain:domain,
                source:source
            }
        });

        var store = Ext.getStore("Greyface.store.AutoWhitelistEmailStore");
        store.reload();
    },

    openAddDomainToAutoWhitelist: function() {
        Ext.create("Greyface.view.autowhitelist.domain.AddDomainWindow", {
            callbackDeleteEntries:this.addDomain
        }).show();
    },
    addDomain: function(domain, source) {
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
                store:"autoWhitelistDomainStore",
                domain:domain,
                source:source
            }
        });

        var store = Ext.getStore("Greyface.store.AutoWhitelistDomainStore");
        store.reload();
    }
});