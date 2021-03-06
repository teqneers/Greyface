Ext.define("Greyface.view.user.admin.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_userAdminPanel",
    actionId:"userAdminPanel",
    title: Greyface.tools.Dictionary.translate("userManagement"),
    selType:'cellmodel',
    plugins:[
        {
            ptype:'rowediting',
            clicksToEdit:2
        }
    ],
    border:false,
    columns: [
        {
            xtype: "actioncolumn",
            width:32,
            resizable:false,
            items:[
                {
                    icon: 'resources/images/delete.png',
                    tooltip: Greyface.tools.Dictionary.translate("delete"),
                    handler: function(grid, rowIndex, colIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        Ext.create("Greyface.view.user.admin.ConfirmDeleteUserWindow", {
                            userRecord: grid.getStore().getAt(rowIndex),
                            store: grid.getStore()
                        }).show();
                    }
                }
            ]
        },
        {
            xtype: "actioncolumn",
            width:32,
            resizable:false,
            items:[
                {
                    icon: 'resources/images/user_edit.png',
                    tooltip: Greyface.tools.Dictionary.translate("setNewUserPassword"),
                    handler: function(grid, rowIndex, colIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        Ext.create("Greyface.view.user.admin.SetUserPasswordWindow", {userRecord:rec}).show();
                    }
                }
            ]
        },
        {
            text:Greyface.tools.Dictionary.translate("userStatus"),
            xtype: 'booleancolumn',
            autoSizeColumn:true,
            dataIndex:'is_admin',
            trueText:Greyface.tools.Dictionary.translate("statusAdmin"),
            falseText:'-',
            editor:{
                xtype:'checkboxfield'
            }
        },
        {
            text: Greyface.tools.Dictionary.translate("username"),
            dataIndex:"username",
            autoSizeColumn:true,
            editor:{
                xtype:'textfield',
                allowBlank: false
            }
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
        actionId:"userAdminPanelPagingToolbar",
        dock:"bottom",
        displayInfo:true
    }]
})