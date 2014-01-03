Ext.define("Greyface.view.whitelist.email.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_whitelistEmailPanel",
    actionId:"whitelistEmailPanel",
    title: "Whitelist email",
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
        actionId:"whitelistEmailPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})