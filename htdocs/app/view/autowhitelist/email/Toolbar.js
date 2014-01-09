Ext.define("Greyface.view.autowhitelist.email.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_autoWhitelistEmailToolbar",
    actionId:"autoWhitelistEmailToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"autoWhitelistToolbarAddEmail",
            text: Greyface.tools.Dictionary.translate("addEmail"),
            icon: "resources/images/email_add.png"
        },
        {
            xtype:"textfield",
            actionId:"autoWhitelistToolbarSearchForEmail",
            emptyText:Greyface.tools.Dictionary.translate("searchValue"),
            fieldLabel: Greyface.tools.Dictionary.translate("searchMail"),
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