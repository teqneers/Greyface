Ext.define("Greyface.view.autowhitelist.email.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_autoWhitelistEmailToolbar",
    actionId:"autoWhitelistEmailToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"autoWhitelistToolbarAddEmail",
            text:"Add Email",
            icon: "resources/images/email_add.png"
        },
        {
            xtype:"textfield",
            actionId:"autoWhitelistToolbarSearchForEmail",
            emptyText:"search value...",
            fieldLabel:"Search for email:",
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"button",
            actionId:"autoWhitelistEmailToolbarCancelFilter",
            text:"",
            icon: "resources/images/cross_red.png",
            hidden:true
        }
    ]
});