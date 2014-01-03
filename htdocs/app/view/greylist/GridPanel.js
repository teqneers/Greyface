Ext.define("Greyface.view.greylist.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_greylistPanel",
    actionId:"greylistPanel",
    title: "Greylist",
    border:false,
    columns: [
        {
            xtype: "actioncolumn",
            width:18,
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
        {
            xtype: "actioncolumn",
            width:18,
            resizable:false,
            items:[
                {
                    icon: 'resources/images/page_white_get.png',  // Use a URL in the icon config
                    tooltip: 'Move to Auto whitelist',
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
        {text: "Sender", dataIndex:"sender_name", autoSizeColumn:true},
        {text: "Domain",dataIndex:"sender_domain", autoSizeColumn:true},
        {text: "Source",dataIndex:"source", autoSizeColumn:true},
        {text: "Recipient",dataIndex:"alias_name", autoSizeColumn:true},
        {text: "First seen",dataIndex:"first_seen", autoSizeColumn:true},
        {text: "User",dataIndex:"username", autoSizeColumn:true}
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