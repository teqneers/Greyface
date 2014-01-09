Ext.define("Greyface.view.blacklist.domain.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_blacklistDomainToolbar",
    actionId:"blacklistDomainToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"blacklistToolbarAddDomain",
            text:Greyface.tools.Dictionary.translate("addDomain"),
            icon: "resources/images/domain_add.png"
        },
        {
            xtype:"textfield",
            actionId:"blacklistToolbarSearchForDomain",
            name:"blacklistToolbarSearchForDomain",
            emptyText:Greyface.tools.Dictionary.translate("searchValue"),
            fieldLabel:Greyface.tools.Dictionary.translate("searchDomain"),
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