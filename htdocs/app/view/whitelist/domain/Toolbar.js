Ext.define("Greyface.view.whitelist.domain.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_whitelistDomainToolbar",
    actionId:"whitelistDomainToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"whitelistToolbarAddDomain",
            text:"Add domain",
            icon: "resources/images/domain_add.png"
        },
        {
            xtype:"textfield",
            actionId:"whitelistToolbarSearchForDomain",
            name:"whitelistToolbarSearchForDomain",
            emptyText:"search value...",
            fieldLabel:"Search for domain:",
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"button",
            actionId:"whitelistDomainToolbarCancelFilter",
            text:"",
            icon: "resources/images/cross_red.png",
            hidden:true
        }
    ]
});