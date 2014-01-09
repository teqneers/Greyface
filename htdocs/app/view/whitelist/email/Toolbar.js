Ext.define("Greyface.view.whitelist.email.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_whitelistEmailToolbar",
    actionId:"whitelistEmailToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"whitelistToolbarAddEmail",
            text:Greyface.tools.Dictionary.translate("addEmail"),
            icon: "resources/images/email_add.png"
        },
        {
            xtype:"textfield",
            actionId:"whitelistToolbarSearchForEmail",
            name:"whitelistToolbarSearchForEmail",
            emptyText:Greyface.tools.Dictionary.translate("searchValue"),
            fieldLabel:Greyface.tools.Dictionary.translate("searchDomain"),
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