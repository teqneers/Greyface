Ext.define("Greyface.view.user.admin.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_userAdminPanel",
    actionId:"userAdminPanel",
    title: Greyface.tools.Dictionary.translate("userManagement"),
    border:false,
    columns: [
        {
            xtype: "actioncolumn",
            width:32,
            resizable:false,
            items:[
                {
                    icon: 'resources/images/delete.png',
                    tooltip: Greyface.tools.Dictionary.translate("delete"),
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
                    tooltip: Greyface.tools.Dictionary.translate("setNewUserPassword"),
                    handler: function(grid, rowIndex, colIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        Ext.create("Greyface.view.user.admin.SetUserPasswordWindow", {userRecord:rec}).show();
                    }
                }
            ]
        },
        {
            text:Greyface.tools.Dictionary.translate("userStatus"),
            xtype: 'templatecolumn',
            tpl: "<tpl if='is_admin == 1'>Admin<tpl else>User</tpl>",
            autoSizeColumn:true
        },
        {text: Greyface.tools.Dictionary.translate("username"), dataIndex:"username", autoSizeColumn:true},
        {text: Greyface.tools.Dictionary.translate("email"),dataIndex:"email", autoSizeColumn:true}
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