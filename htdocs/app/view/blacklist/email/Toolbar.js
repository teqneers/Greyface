Ext.define("Greyface.view.blacklist.email.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_blacklistEmailToolbar",
    actionId:"blacklistEmailToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"blacklistToolbarAddEmail",
            text:Greyface.tools.Dictionary.translate("addEmail"),
            icon: "resources/images/email_add.png"
        },
        {
            xtype:"textfield",
            actionId:"blacklistToolbarSearchForEmail",
            name:"blacklistToolbarSearchForEmail",
            emptyText:Greyface.tools.Dictionary.translate("searchValue"),
            fieldLabel:Greyface.tools.Dictionary.translate("SearchEmail"),
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