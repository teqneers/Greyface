Ext.define("Greyface.view.user.admin.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_userAdminToolbar",
    actionId:"userAdminToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"userAdminAddUser",
            text:"Add user",
            icon: "resources/images/user_add.png"
        },
        {
            xtype:"textfield",
            actionId:"userAdminToolbarSearchForUser",
            name:"userAdminToolbarSearchForUser",
            emptyText:"search value...",
            fieldLabel:"Search for user:",
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