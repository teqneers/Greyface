Ext.define("Greyface.view.autowhitelist.email.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_autoWhitelistEmailPanel",
    actionId:"autoWhitelistEmailPanel",
    title: "Auto whitelist email",
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
        {text: "Sender", dataIndex:"sender_name", autoSizeColumn:true, align:"right"},
        {text: "@", xtype:"templatecolumn", tpl:"@", width:20, align:"center"},
        {text: "Domain", dataIndex:"sender_domain", autoSizeColumn:true},
        {text: "Source", dataIndex:"src", autoSizeColumn:true},
        {text: "First seen", dataIndex:"first_seen", autoSizeColumn:true},
        {text: "Last seen", dataIndex:"last_seen", autoSizeColumn:true}
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
        actionId:"autoWhitelistEmailPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})