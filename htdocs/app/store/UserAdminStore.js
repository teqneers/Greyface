Ext.define("Greyface.store.UserAdminStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.UserModel",
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
            store:"userAdminStore"
        },
        reader: {
            type: "json",
            root:"rows",
            totalProperty:"totalRows"
        }
    }
});