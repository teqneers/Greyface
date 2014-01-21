Ext.define("Greyface.store.UserListStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.UserFilterModel",
    autoLoad:true,
    autoSync: true,
    remoteFilter: false,
    proxy: {
        type:"ajax",
        api: {
            read: "api/CRUDRouter.php?action=getUsers"
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