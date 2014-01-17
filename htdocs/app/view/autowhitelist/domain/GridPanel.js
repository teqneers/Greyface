Ext.define("Greyface.view.autowhitelist.domain.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_autoWhitelistDomainPanel",
    actionId:"autoWhitelistDomainPanel",
    title: Greyface.tools.Dictionary.translate("autoWhitelist") + ": " + Greyface.tools.Dictionary.translate("domains"),
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
        {text: Greyface.tools.Dictionary.translate("domain"), dataIndex:"sender_domain", autoSizeColumn:true},
        {text: Greyface.tools.Dictionary.translate("source"), dataIndex:"src", autoSizeColumn:true},
        {xtype:"datecolumn", text: Greyface.tools.Dictionary.translate("firstSeen"), dataIndex:"first_seen", autoSizeColumn:true},
        {xtype:"datecolumn", text: Greyface.tools.Dictionary.translate("lastSeen"), dataIndex:"last_seen", autoSizeColumn:true}
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
        actionId:"autoWhitelistDomainPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})