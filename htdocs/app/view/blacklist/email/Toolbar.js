Ext.define("Greyface.view.blacklist.email.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_blacklistEmailToolbar",
    actionId:"blacklistEmailToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"blacklistToolbarAddEmail",
            text:"Add Email",
            icon: "resources/images/email_add.png"
        },
        {
            xtype:"textfield",
            actionId:"blacklistToolbarSearchForEmail",
            name:"blacklistToolbarSearchForEmail",
            emptyText:"search value...",
            fieldLabel:"Search for email:",
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"button",
            actionId:"blacklistEmailToolbarCancelFilter",
            text:"",
            icon: "resources/images/cross_red.png",
            hidden:true
        }
    ]
});