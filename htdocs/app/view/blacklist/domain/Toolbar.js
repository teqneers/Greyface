Ext.define("Greyface.view.blacklist.domain.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_blacklistDomainToolbar",
    actionId:"blacklistDomainToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"blacklistToolbarAddDomain",
            text:"Add domain",
            icon: "resources/images/domain_add.png"
        },
        {
            xtype:"textfield",
            actionId:"blacklistToolbarSearchForDomain",
            name:"blacklistToolbarSearchForDomain",
            emptyText:"search value...",
            fieldLabel:"Search for domain:",
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"button",
            actionId:"blacklistDomainToolbarCancelFilter",
            text:"",
            icon: "resources/images/cross_red.png",
            hidden:true
        }
    ]
});