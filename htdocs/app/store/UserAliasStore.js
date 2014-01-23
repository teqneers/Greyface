Ext.define("Greyface.store.UserAliasStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.AliasModel",
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
            store:"userAliasStore"
        },
        reader: {
            type: "json",
            root:"rows",
            totalProperty:"totalRows"
        }
    }
});