Ext.define("Greyface.view.user.alias.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_userAliasToolbar",
    actionId:"userAliasToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"userAliasToolbarAddAlias",
            text:Greyface.tools.Dictionary.translate("addAlias"),
            icon: "resources/images/user_add.png"
        },
        {
            xtype:"combo",
            actionId:"userAliasToolbarFilterBy",
            multiselect:false,
            editable: false,
            typeAhead:false,
            fieldLabel:Greyface.tools.Dictionary.translate("filterBy"),
            labelAlign:"right",
            displayField: "username",
            valueField: "user_id",
            forceSelection: true
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