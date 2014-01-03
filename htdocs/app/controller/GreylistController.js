Ext.define("Greyface.controller.GreylistController", {
    extend: "Ext.app.Controller",
    refs: [
        { ref: "grid", selector: "panel[actionId=greylistPanel]" },
        { ref: "gridPagingToolbar", selector: "pagingtoolbar[actionId=greylistPanelPagingToolbar]" },
        { ref: "greylistFilterTextfield", selector: "textfield[actionId=greylistToolbarFulltext]" },
        { ref: "greylistFilterCancelButton", selector: "button[actionId=greylistToolbarCancelFilter]" }
    ],
    views: [
        "Greyface.view.greylist.GridPanel",
        "Greyface.view.greylist.Toolbar"
    ],
    stores:["Greyface.store.GreylistStore"],
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
                select: function(){console.log("greylistToolbarFilterBy")}
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
        store.load();
        this.getGrid().reconfigure(store)
        this.getGridPagingToolbar().bindStore(store);
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
            store.clearFilter();
            this.getGreylistFilterCancelButton().hide();
        } else {
            this.getGreylistFilterCancelButton().show();
            store.clearFilter(true);
            store.filter([
                {property:"sender_name", value:"%"+filterValue+"%"},
                {property:"sender_domain", value:"%"+filterValue+"%"},
                {property:"src", value:"%"+filterValue+"%"},
                {property:"recipient", value:"%"+filterValue+"%"},
                {property:"first_seen", value:"%"+filterValue+"%"},
                {property:"username", value:"%"+filterValue+"%"}
            ]);
        }
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
            method: "GET",
            params: {
                store:"greylistStore", toDay:day+1, toMonth:month+1, toYear:year
            }
        });

        var store = Ext.getStore("Greyface.store.GreylistStore");
        store.reload();
    }
});