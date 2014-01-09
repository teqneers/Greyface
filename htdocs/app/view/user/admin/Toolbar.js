Ext.define("Greyface.view.user.admin.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_userAdminToolbar",
    actionId:"userAdminToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"userAdminAddUser",
            text:Greyface.tools.Dictionary.translate("addUser"),
            icon: "resources/images/user_add.png"
        },
        {
            xtype:"textfield",
            actionId:"userAdminToolbarSearchForUser",
            name:"userAdminToolbarSearchForUser",
            emptyText:Greyface.tools.Dictionary.translate("searchValue"),
            fieldLabel:Greyface.tools.Dictionary.translate("searchUser"),
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"button",
            actionId:"userAdminToolbarCancelFilter",
            text:"",
            icon: "resources/images/cross_red.png",
            hidden:true
        }
    ]
});