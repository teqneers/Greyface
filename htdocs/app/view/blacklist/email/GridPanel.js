Ext.define("Greyface.view.blacklist.email.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_blacklistEmailPanel",
    actionId:"blacklistEmailPanel",
    title: Greyface.tools.Dictionary.translate("blacklist") + ": " + Greyface.tools.Dictionary.translate("email"),
    border:false,
    selType:'cellmodel',
    plugins:[
        {
            ptype:'rowediting',
            clicksToEdit:2
        }
    ],
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
        {
            text: Greyface.tools.Dictionary.translate("email"),
            dataIndex:"email",
            autoSizeColumn:true,
            editor:{
                xtype:'textfield',
                allowBlank: false,
                vtype:'email'
            }
        }
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
        actionId:"blacklistEmailPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})