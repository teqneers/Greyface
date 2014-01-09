Ext.define("Greyface.view.autowhitelist.domain.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_autoWhitelistDomainToolbar",
    actionId:"autoWhitelistDomainToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"autoWhitelistToolbarAddDomain",
            text: Greyface.tools.Dictionary.translate("addDomain"),
            icon: "resources/images/domain_add.png"
        },
        {
            xtype:"textfield",
            actionId:"autoWhitelistToolbarSearchForDomain",
            name:"autoWhitelistToolbarSearchForDomain",
            emptyText:Greyface.tools.Dictionary.translate("searchValue"),
            fieldLabel:Greyface.tools.Dictionary.translate("searchDomain"),
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