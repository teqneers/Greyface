Ext.define("Greyface.view.user.alias.GridPanel",{
    extend:"Ext.grid.GridPanel",
    xtype:"gf_userAliasPanel",
    actionId:"userAliasPanel",
    title: "Alias",
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
                        console.log("delete" + grid + ", " + rowIndex + ", " + colIndex);
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
                    icon: 'resources/images/user_edit.png',  // Use a URL in the icon config
                    tooltip: 'User edit',
                    handler: function(grid, rowIndex, colIndex) {
                        console.log("user edit" + grid + ", " + rowIndex + ", " + colIndex);
                    }
                }
            ]
        },
        {text: "Email",dataIndex:"email", autoSizeColumn:true},
        {text: "Username", dataIndex:"username", autoSizeColumn:true}
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