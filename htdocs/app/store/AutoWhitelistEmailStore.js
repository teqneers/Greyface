Ext.define("Greyface.store.AutoWhitelistEmailStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.AutoWhitelistEmailModel",
    autoLoad:true,
    autoSync: true,
    remoteSort:true,
    remoteFilter:true,
    pageSize:100,
    sorters: [
        {
            property: "sender_name",
            direction: "ASC"
        }
    ],
    filters: [
    ],
    proxy: {
        type:"ajax",
        api: {
            read: "api/CRUDRouter.php?action=read",
            update: "api/CRUDRouter.php?action=update"
        },
        extraParams: {
            store:"autoWhitelistEmailStore"
        },
        reader: {
            type: "json",
            root:"rows",
            totalProperty:"totalRows"
        }
    },

    onUpdateRecords: function(records, operation, success) {
        if (operation.action == "update") {
            this.reload();
        }
    }
});