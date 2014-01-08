Ext.define("Greyface.store.UserFilterStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.UserFilterModel",
    autoLoad:true,
    autoSync: true,
    remoteFilter: false,
    proxy: {
        type:"ajax",
        api: {
            read: "api/CRUDRouter.php?action=getGreylistFilter"
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