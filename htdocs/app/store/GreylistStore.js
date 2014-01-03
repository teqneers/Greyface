Ext.define("Greyface.store.GreylistStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.GreylistModel",
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
//            create: "api/CRUDRouter.php?action=create",
            read: "api/CRUDRouter.php?action=read",
//            update: "api/CRUDRouter.php?action=update",
            destroy: "api/CRUDRouter.php?action=destroy"
        },
        extraParams: {
            store:"greylistStore"
        },
        reader: {
            type: "json",
            root:"rows",
            totalProperty:"totalRows"
        }
    }
});