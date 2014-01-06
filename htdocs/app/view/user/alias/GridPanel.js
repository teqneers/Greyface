Ext.define("Greyface.view.user.alias.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_userAliasPanel",
    actionId:"userAliasPanel",
    title: "Alias",
    border:false,
    columns: [
        {
            xtype: "actioncolumn",
            width:32,
            resizable:false,
            items:[
                {
                    icon: 'resources/images/delete.png',  // Use a URL in the icon config
                    tooltip: 'Delete',
                    handler: function(grid, rowIndex, colIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        rec.deleteItem();
                        grid.getStore().reload();
                    }
                }
            ]
        },
        {text: "Email alias",dataIndex:"email", autoSizeColumn:true},
        {text: "Username", dataIndex:"username", autoSizeColumn:true}
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
        actionId:"userAliasPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})