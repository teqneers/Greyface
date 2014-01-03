Ext.define("Greyface.view.whitelist.email.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_whitelistEmailToolbar",
    actionId:"whitelistEmailToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"whitelistToolbarAddEmail",
            text:"Add Email",
            icon: "resources/images/email_add.png"
        },
        {
            xtype:"textfield",
            actionId:"whitelistToolbarSearchForEmail",
            name:"whitelistToolbarSearchForEmail",
            emptyText:"search value...",
            fieldLabel:"Search for email:",
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"button",
            actionId:"whitelistEmailToolbarCancelFilter",
            text:"",
            icon: "resources/images/cross_red.png",
            hidden:true
        }
    ]
});