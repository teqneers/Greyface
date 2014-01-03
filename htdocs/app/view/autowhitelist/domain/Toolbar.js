Ext.define("Greyface.view.autowhitelist.domain.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_autoWhitelistDomainToolbar",
    actionId:"autoWhitelistDomainToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"autoWhitelistToolbarAddDomain",
            text:"Add domain",
            icon: "resources/images/domain_add.png"
        },
        {
            xtype:"textfield",
            actionId:"autoWhitelistToolbarSearchForDomain",
            name:"autoWhitelistToolbarSearchForDomain",
            emptyText:"search value...",
            fieldLabel:"Search for domain:",
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"button",
            actionId:"autoWhitelistDomainToolbarCancelFilter",
            text:"",
            icon: "resources/images/cross_red.png",
            hidden:true
        }
    ]
});