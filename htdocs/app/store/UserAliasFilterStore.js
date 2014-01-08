Ext.define("Greyface.store.UserAliasFilterStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.UserFilterModel",
    autoLoad:true,
    autoSync: true,
    remoteFilter: false,
    proxy: {
        type:"ajax",
        api: {
            read: "api/CRUDRouter.php?action=getUserAliasFilter"
        },
        extraParams: {
            store:"userFilterStore"
        },
        reader: {
            type: "json",
            root:"rows"
        }
    }
});