Ext.define("Greyface.view.user.alias.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_userAliasPanel",
    actionId:"userAliasPanel",
    title: Greyface.tools.Dictionary.translate("aliasManagement"),
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
            text: Greyface.tools.Dictionary.translate("alias"),
            dataIndex:"email",
            autoSizeColumn:true,
            editor:{
                xtype:'textfield',
                allowBlank: false,
                vtype:'email'
            }
        },
        {
            text: Greyface.tools.Dictionary.translate("username"),
            dataIndex:"username",
            autoSizeColumn:true,
            editor:{
                xtype:'combobox',
                store: Ext.create("Greyface.store.UserListStore"),
                displayField:'username',
//                allowBlank: false,
                typeAhead:true,
                typeAheadDelay:100,
                multiselect:false,
                queryCaching:false,
                minChars:1,
                forceSelection:true,
//                listeners:{
//                    select: function(combo, records, eOpts){
//                        console.log( records[0].get('username') );
//                        console.log( records[0].get('user_id') );
//                        console.log( eOpts );
//                        console.log( this );
//                        // ich glaube die user_id muss hier komplett rausfliegen (redundant)
//                        // schmeisse dann auch aus dem model und der db_store_klasse raus!
//
//                    }
//                }
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
        actionId:"userAliasPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})