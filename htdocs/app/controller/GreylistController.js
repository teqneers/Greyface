Ext.define("Greyface.controller.GreylistController", {
    extend: "Ext.app.Controller",
    refs: [
        { ref: "grid", selector: "panel[actionId=greylistPanel]" },
        { ref: "gridPagingToolbar", selector: "pagingtoolbar[actionId=greylistPanelPagingToolbar]" },
        { ref: "greylistFilterTextfield", selector: "textfield[actionId=greylistToolbarFulltext]" },
        { ref: "greylistFilterCancelButton", selector: "button[actionId=greylistToolbarCancelFilter]" },
        { ref: "greylistUserFilterByCombobox", selector: "combo[actionId=greylistToolbarFilterBy]" }
    ],
    views: [
        "Greyface.view.greylist.GridPanel",
        "Greyface.view.greylist.Toolbar"
    ],
    stores:[
        "Greyface.store.GreylistStore",
        "Greyface.store.UserFilterStore",
        "Greyface.store.KeyValueLocalStorageStore"
    ],
    config: {
        firstload: true
    },
    init: function () {
        this.control({
            // wire up the stores to the datagrid in view
            "panel[actionId=mainScreen]": {
                beforerender: this.wireupStore
            },

            // wire up events to toolbar controls
            "button[actionId=greylistToolbarDeleteEntriesByTime]": {
                click: this.openDatePickerWindow
            },
            "combobox[actionId=greylistToolbarFilterBy]": {
                change: function(box, newValue, oldValue, eOpts){
                    var store = Ext.getStore("Greyface.store.GreylistStore");
                    if(newValue != "show_all" && newValue != "show_unassigned") {
                        store.addFilter([{id:"user_id", property:"user_id", value:newValue}],false);
                    } else if(newValue == "show_all") {
                        store.filters.removeAtKey("user_id"); // !Workaround because a bug in ExtJS! //
                    } else if(newValue == "show_unassigned") {
                        store.addFilter([{id:"user_id", property:"user_id", value:"show_unassigned"}], false);
                    }
                    this.getGreylistFilterTextfield().fireEvent("change");
                },
                expand: function(field, eOpts){
                    Ext.getStore("Greyface.store.UserFilterStore").reload();
                }
            },


            // Greylist Filter
            "textfield[actionId=greylistToolbarFulltext]": {
                change: this.initGreylistFilter,
                specialkey: function(textfield, eventObjet) {
                    if(eventObjet.getKey() == eventObjet.ESC) {
                        textfield.setValue("");
                    }
                }
            },
            "button[actionId=greylistToolbarCancelFilter]": {
                click: function() {this.getGreylistFilterTextfield().setValue("")}
            }
        });
    },

    wireupStore: function () {
        var store = Ext.getStore("Greyface.store.GreylistStore");
        store.load(); // We need to load this store on startup
        this.getGrid().reconfigure(store)
        this.getGridPagingToolbar().bindStore(store);

        // wires up the store for the combobox which shows all users, after which the grid can be filtered.
        var userFilterStore = Ext.getStore("Greyface.store.UserFilterStore");
        userFilterStore.load(); // We need to load this store on startup anyway!
        this.getGreylistUserFilterByCombobox().bindStore(userFilterStore);
        var scope = this

        userFilterStore.on('load', this.useSavedUserFilter, scope);
        this.getGreylistUserFilterByCombobox().on('change', this.saveUserFilter, scope);



    },
    useSavedUserFilter: function(store, records, successful, eOpts) {
        if(successful === false) {
            return
        }
        // only executes on very first load event!
        if( this.getFirstload() ) {
            this.setFirstload(false)

            var keyValueLocalStorage =  Ext.getStore("Greyface.store.KeyValueLocalStorageStore");
            var userFilterStore = Ext.getStore("Greyface.store.UserFilterStore");
            if( keyValueLocalStorage.has('GreylistUserFilter') ) {
                if ( userFilterStore.find('username', keyValueLocalStorage.get('GreylistUserFilter')) > -1 ) {
                    var record = userFilterStore.getAt(userFilterStore.find('username', keyValueLocalStorage.get('GreylistUserFilter')))
                    this.getGreylistUserFilterByCombobox().select(record)
                }
            }
        }
    },
    saveUserFilter: function(combo, newValue, oldValue, eOpts) {
        var keyValueLocalStorage =  Ext.getStore("Greyface.store.KeyValueLocalStorageStore");
        keyValueLocalStorage.store('GreylistUserFilter',combo.getRawValue())
    },


    // Filtering Greylist
    initGreylistFilter: function() {
        var task = this.createGreylistFilterTask();
        task.delay(300)
    },
    toggleGreylistFilter: function() {
        var store = Ext.getStore("Greyface.store.GreylistStore");
        var filterValue= this.getGreylistFilterTextfield().getValue();
        if(filterValue == ""){
            store.filters.removeAtKey("sender_name");
            store.filters.removeAtKey("sender_domain");
            store.filters.removeAtKey("src");
            store.filters.removeAtKey("recipient");
            store.filters.removeAtKey("first_seen");
            store.filters.removeAtKey("username");
        } else {
            this.getGreylistFilterCancelButton().show();
            store.addFilter([
                {id:"sender_name", property:"sender_name", value:"%"+filterValue+"%"},
                {id:"sender_domain", property:"sender_domain", value:"%"+filterValue+"%"},
                {id:"src", property:"src", value:"%"+filterValue+"%"},
                {id:"recipient", property:"recipient", value:"%"+filterValue+"%"},
                {id:"first_seen", property:"first_seen", value:"%"+filterValue+"%"},
                {id:"username", property:"username", value:"%"+filterValue+"%"}
            ], false);
        }
        store.load();
    },
    createGreylistFilterTask: function() {
        if(!Greyface.tools.Registry.exists(("greylistFilterTextfield"))) {
            var task = new Ext.util.DelayedTask(this.toggleGreylistFilter, this);
            Greyface.tools.Registry.set("greylistFilterTextfield", task);
        } else {
            var task = Greyface.tools.Registry.get("greylistFilterTextfield")
        }
        return task;
    },

    // Opens Window to delete Entries and define a "delete to date"
    openDatePickerWindow: function() {
        Ext.create("Greyface.view.greylist.DeleteEntriesWindow", {
            callbackDeleteEntries:this.deleteEntriesToDate}).show();
    },

    // Sends AJAX request to server, who will delete all entries which are smaller than the given date.
    deleteEntriesToDate: function(date) {
        var day = date.getUTCDate();
        var month = date.getUTCMonth();
        var year = date.getUTCFullYear();

        Ext.Ajax.request({
            url: "api/CRUDRouter.php?action=deleteTo",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse.data[0])
            },
            failure: function(response, opts) {
                // nothing
            },
            method: "POST",
            params: {
                store:"greylistStore", toDay:day+1, toMonth:month+1, toYear:year
            }
        });

        var store = Ext.getStore("Greyface.store.GreylistStore");
        store.reload();
    }
});