Ext.define("Greyface.controller.BlacklistController", {
    extend: "Ext.app.Controller",
    refs: [
        { ref: "emailGrid", selector: "panel[actionId=blacklistEmailPanel]" },
        { ref: "emailGridPagingToolbar", selector: "pagingtoolbar[actionId=blacklistEmailPanelPagingToolbar]" },
        { ref: "domainGrid", selector: "panel[actionId=blacklistDomainPanel]" },
        { ref: "domainGridPagingToolbar", selector: "pagingtoolbar[actionId=blacklistDomainPanelPagingToolbar]" },
        { ref: "eMailFilterTextfield", selector: "textfield[actionId=blacklistToolbarSearchForEmail]" },
        { ref: "eMailFilterCancelButton", selector: "button[actionId=blacklistEmailToolbarCancelFilter]" },
        { ref: "domainFilterTextfield", selector: "textfield[actionId=blacklistToolbarSearchForDomain]" },
        { ref: "domainFilterCancelButton", selector: "button[actionId=blacklistDomainToolbarCancelFilter]" }
    ],
    views: [
        "Greyface.view.blacklist.email.GridPanel",
        "Greyface.view.blacklist.email.Toolbar",
        "Greyface.view.blacklist.domain.GridPanel",
        "Greyface.view.blacklist.domain.Toolbar"
    ],
    stores:[
        "Greyface.store.BlacklistEmailStore",
        "Greyface.store.BlacklistDomainStore"
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
            "button[actionId=blacklistToolbarAddEmail]": {
                click: function(){
                    this.openAddEmailToBlacklist();
                }
            },
            // Email Filter
            "textfield[actionId=blacklistToolbarSearchForEmail]": {
                change: this.initEmailFilter,
                specialkey: function(textfield, eventObjet) {
                    if(eventObjet.getKey() == eventObjet.ESC) {
                        textfield.setValue("");
                    }
                }
            },
            "button[actionId=blacklistEmailToolbarCancelFilter]": {
                click: function() {this.getEMailFilterTextfield().setValue("")}
            },


            // Add Domain
            "button[actionId=blacklistToolbarAddDomain]": {
                click: function(){
                    this.openAddDomainToBlacklist();
                }
            },
            // Domain Filter
            "textfield[actionId=blacklistToolbarSearchForDomain]": {
                change: this.initDomainFilter,
                specialkey: function(textfield, eventObjet) {
                    if(eventObjet.getKey() == eventObjet.ESC) {
                        textfield.setValue("");
                    }
                }
            },
            "button[actionId=blacklistDomainToolbarCancelFilter]": {
                click: function() {this.getDomainFilterTextfield().setValue("")}
            }
        });
    },

    wireupEmailStore: function () {
        var store = Ext.getStore("Greyface.store.BlacklistEmailStore");
        store.load();
        this.getEmailGrid().reconfigure(store)
        this.getEmailGridPagingToolbar().bindStore(store);
    },
    wireupDomainStore: function () {
        var store = Ext.getStore("Greyface.store.BlacklistDomainStore");
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
        var store = Ext.getStore("Greyface.store.BlacklistEmailStore");
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
        if(!Greyface.tools.Registry.exists(("BlacklistEmailFilterTextfield"))) {
            var task = new Ext.util.DelayedTask(this.toggleEmailFilter, this);
            Greyface.tools.Registry.set("BlacklistEmailFilterTextfield", task);
        } else {
            var task = Greyface.tools.Registry.get("BlacklistEmailFilterTextfield")
        }
        return task;
    },

    // Filtering Domain...
    initDomainFilter: function() {
        var task = this.createDomainFilterTask();
        task.delay(300)
    },
    toggleDomainFilter: function() {
        var store = Ext.getStore("Greyface.store.BlacklistDomainStore");
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
        if(!Greyface.tools.Registry.exists(("BlacklistDomainFilterTextfield"))) {
            var task = new Ext.util.DelayedTask(this.toggleDomainFilter, this);
            Greyface.tools.Registry.set("BlacklistDomainFilterTextfield", task);
        } else {
            var task = Greyface.tools.Registry.get("BlacklistDomainFilterTextfield")
        }
        return task;
    },

    // Add Items
    openAddEmailToBlacklist: function() {
        Ext.create("Greyface.view.blacklist.email.AddEmailWindow", {
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
            method: "GET",
            params: {
                store:"blacklistEmailStore",
                email:email
            }
        });

        var store = Ext.getStore("Greyface.store.BlacklistEmailStore");
        store.reload();
    },

    openAddDomainToBlacklist: function() {
        Ext.create("Greyface.view.blacklist.domain.AddDomainWindow", {
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
            method: "GET",
            params: {
                store:"blacklistDomainStore",
                domain:domain
            }
        });

        var store = Ext.getStore("Greyface.store.BlacklistDomainStore");
        store.reload();
    }
});