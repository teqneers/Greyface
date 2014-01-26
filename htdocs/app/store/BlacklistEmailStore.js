Ext.define("Greyface.store.BlacklistEmailStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.BlacklistEmailModel",
    autoLoad:true,
    autoSync: true,
    remoteSort:true,
    remoteFilter:true,
    pageSize:100,
    sorters: [
        {
            property: "email",
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
            store:"blacklistEmailStore"
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