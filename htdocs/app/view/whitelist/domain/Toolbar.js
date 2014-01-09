Ext.define("Greyface.view.whitelist.domain.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_whitelistDomainToolbar",
    actionId:"whitelistDomainToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"whitelistToolbarAddDomain",
            text:Greyface.tools.Dictionary.translate("addDomain"),
            icon: "resources/images/domain_add.png"
        },
        {
            xtype:"textfield",
            actionId:"whitelistToolbarSearchForDomain",
            name:"whitelistToolbarSearchForDomain",
            emptyText:Greyface.tools.Dictionary.translate("searchValue"),
            fieldLabel:Greyface.tools.Dictionary.translate("searchDomain"),
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