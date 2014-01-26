Ext.define("Greyface.store.WhitelistDomainStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.WhitelistDomainModel",
    autoLoad:true,
    autoSync: true,
    remoteSort:true,
    remoteFilter:true,
    pageSize:100,
    sorters: [
        {
            property: "domain",
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
            store:"whitelistDomainStore"
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