Ext.define("Greyface.view.autowhitelist.email.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_autoWhitelistEmailPanel",
    actionId:"autoWhitelistEmailPanel",
    title: Greyface.tools.Dictionary.translate("autoWhitelist") + ": " + Greyface.tools.Dictionary.translate("emails"),
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
            text: Greyface.tools.Dictionary.translate("sender"),
            dataIndex:"sender_name",
            autoSizeColumn:true,
            align:"right",
            editor:{
                xtype:'textfield',
                allowBlank: false
            }
        },
        {
            text: "@", xtype:"templatecolumn",
            tpl:"@", width:20,
            align:"center"
        },
        {
            text: Greyface.tools.Dictionary.translate("domain"),
            dataIndex:"sender_domain",
            autoSizeColumn:true,
            editor:{
                xtype:'textfield',
                allowBlank: false
            }
        },
        {
            text: Greyface.tools.Dictionary.translate("source"),
            dataIndex:"src",
            autoSizeColumn:true,
            editor:{
                xtype:'textfield',
                allowBlank: false
            }
        },
        {
            xtype:"datecolumn",
            text: Greyface.tools.Dictionary.translate("firstSeen"),
            dataIndex:"first_seen",
            autoSizeColumn:true
        },
        {
            xtype:"datecolumn",
            text: Greyface.tools.Dictionary.translate("lastSeen"),
            dataIndex:"last_seen",
            autoSizeColumn:true
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
        actionId:"autoWhitelistEmailPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})