Ext.define("Greyface.view.greylist.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_greylistPanel",
    actionId:"greylistPanel",
    title: Greyface.tools.Dictionary.translate("greylist"),
    border:false,
    columns: [
        {
            xtype: "actioncolumn",
            width:18,
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
        {
            xtype: "actioncolumn",
            width:18,
            resizable:false,
            items:[
                {
                    icon: 'resources/images/page_white_get.png',  // Use a URL in the icon config
                    tooltip: Greyface.tools.Dictionary.translate("moveToAutoWhitelist"),
                    handler: function(grid, rowIndex, colIndex) {
                        console.log("Move to Auto whitelist" + grid + ", " + rowIndex + ", " + colIndex);
                        var rec = grid.getStore().getAt(rowIndex);
                        var rec = grid.getStore().getAt(rowIndex);
                        rec.toWhitelist();
                        grid.getStore().reload();

                    }
                }
            ]
        },
        {text:Greyface.tools.Dictionary.translate("sender"), dataIndex:"sender_name", autoSizeColumn:true},
        {text:Greyface.tools.Dictionary.translate("domain"), dataIndex:"sender_domain", autoSizeColumn:true},
        {text:Greyface.tools.Dictionary.translate("source"), dataIndex:"source", autoSizeColumn:true},
        {text:Greyface.tools.Dictionary.translate("recipient"), dataIndex:"alias_name", autoSizeColumn:true},
        {text:Greyface.tools.Dictionary.translate("firstSeen"), dataIndex:"first_seen", autoSizeColumn:true},
        {text:Greyface.tools.Dictionary.translate("username"), dataIndex:"username", autoSizeColumn:true}
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
        actionId:"greylistPanelPagingToolbar",
        store:Ext.getStore("Greyface.store.GreylistStore"),
        dock:"bottom",
        displayInfo:true
    }]
})