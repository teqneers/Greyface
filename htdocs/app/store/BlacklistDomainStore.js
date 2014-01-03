Ext.define("Greyface.store.BlacklistDomainStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.BlacklistDomainModel",
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
            create: "api/CRUDRouter.php?action=create",
            read: "api/CRUDRouter.php?action=read",
            update: "api/CRUDRouter.php?action=update",
            destroy: "api/CRUDRouter.php?action=destroy"
        },
        extraParams: {
            store:"blacklistDomainStore"
        },
        reader: {
            type: "json",
            root:"rows",
            totalProperty:"totalRows"
        }
    }
});