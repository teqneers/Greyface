Ext.define("Greyface.view.user.alias.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_userAliasToolbar",
    actionId:"userAliasToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"userAliasToolbarAddAlias",
            text:"Add alias",
            icon: "resources/images/user_add.png"
        },
        {
            xtype:"button",
            actionId:"userAliasToolbarImportData",
            text:"Add alias",
            icon: "resources/images/user_add.png"
        },
        {
            xtype:"combo",
            actionId:"userAliasToolbarFilterBy",
            multiselect:false,
            editable: false,
            typeAhead:false,
            fieldLabel:"Filter by:",
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"textfield",
            actionId:"userAliasToolbarSearchForAlias",
            emptyText:"search value...",
            fieldLabel:"Search for alias:",
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"button",
            actionId:"userAliasToolbarCancelFilter",
            text:"",
            icon: "resources/images/cross_red.png",
            hidden:true
        }
    ]
});