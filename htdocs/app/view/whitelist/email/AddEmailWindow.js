Ext.define("Greyface.view.whitelist.email.AddEmailWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_whitelistAddEmailWindow",
    modal:true,
    title: Greyface.tools.Dictionary.translate("whitelist") + ": " + Greyface.tools.Dictionary.translate("addEmail"),
    resizable:false,
    layout:"fit",
    config: {
        callbackDeleteEntries:""
    },
    constructor: function(cfg) {
        this.callParent(arguments);
    },
    items: [
        {
            xtype: 'form',
            bodyPadding: 10,
            border:false,
            defaultType: 'textfield',
            items: [
                {
                    fieldLabel: Greyface.tools.Dictionary.translate("email"),
                    name: 'email',
                    allowBlank: false,
                    vtype: 'email'
                }
            ],
            buttons: [
                {
                    text: Greyface.tools.Dictionary.translate("add"),
                    formBind: true,
                    disabled: true,
                    handler: function(){
                        var form = this.up('form').getForm();
                        var email = form.findField("email").getValue();
                        this.up("window").callbackDeleteEntries(email);
                        this.up('window').destroy();
                    }
                }
            ]
        }
    ]

});