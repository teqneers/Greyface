Ext.define("Greyface.view.user.admin.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_userAdminPanel",
    actionId:"userAdminPanel",
    title: "Usermanagement",
    border:false,
    columns: [
        {
            xtype: "actioncolumn",
            width:32,
            resizable:false,
            items:[
                {
                    icon: 'resources/images/delete.png',
                    tooltip: 'Delete',
                    handler: function(grid, rowIndex, colIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        rec.deleteItem();
                        grid.getStore().reload();
                    }
                }
            ]
        },
        {
            xtype: "actioncolumn",
            width:32,
            resizable:false,
            items:[
                {
                    icon: 'resources/images/user_edit.png',
                    tooltip: 'Set password',
                    handler: function(grid, rowIndex, colIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        Ext.create("Greyface.view.user.admin.SetUserPasswordWindow", {userRecord:rec}).show();
                    }
                }
            ]
        },
        {
            text:"Type",
            xtype: 'templatecolumn',
            tpl: "<tpl if='is_admin == 1'>Admin<tpl else>User</tpl>",
            autoSizeColumn:true
        },
        {text: "Username", dataIndex:"username", autoSizeColumn:true},
        {text: "Email",dataIndex:"email", autoSizeColumn:true}
    ],
    viewConfig: {
        listeners: {
            refresh: function(dataview) {
                Ext.each(dataview.panel.columns, function(column) {
                    if (column.autoSizeColumn === true)
                        column.autoSize();
                })
            }
        }
    },
    dockedItems:[{
        xtype:"pagingtoolbar",
        actionId:"userAdminPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})