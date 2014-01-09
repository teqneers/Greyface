Ext.define("Greyface.view.whitelist.domain.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_whitelistDomainPanel",
    actionId:"whitelistDomainPanel",
    title: Greyface.tools.Dictionary.translate("whitelist") + ": " + Greyface.tools.Dictionary.translate("domains"),
    border:false,
    columns: [
        {
            xtype: "actioncolumn",
            width:32,
            resizable:false,
            items:[
                {
                    icon: 'resources/images/delete.png',  // Use a URL in the icon config
                    tooltip: Greyface.tools.Dictionary.translate("delete"),
                    handler: function(grid, rowIndex, colIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        rec.deleteItem();
                        grid.getStore().reload();
                    }
                }
            ]
        },
        {text: Greyface.tools.Dictionary.translate("domain"), dataIndex:"domain", autoSizeColumn:true}
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
        actionId:"whitelistDomainPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})