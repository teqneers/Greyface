Ext.define("Greyface.view.autowhitelist.email.AddEmailWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_autowhitelistAddEmailWindow",
    modal:true,
    title: Greyface.tools.Dictionary.translate("autoWhitelist") + ": " + Greyface.tools.Dictionary.translate("addEmail"),
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
                    fieldLabel: Greyface.tools.Dictionary.translate("sender"),
                    name: 'sender',
                    allowBlank: false
                },
                {
                    fieldLabel: Greyface.tools.Dictionary.translate("domain"),
                    name: 'domain',
                    allowBlank: false
                },
                {
                    fieldLabel: Greyface.tools.Dictionary.translate("source"),
                    name: 'source',
                    allowBlank: false,
                    regex: /([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4}|(\d{1,3}\.){3}\d{1,3}/, //@TODO replace with reusable vtype!
                    regexText: Greyface.tools.Dictionary.translate("hasToBeValidIp4/6")
                }
            ],
            buttons: [
                {
                    text: Greyface.tools.Dictionary.translate("add"),
                    formBind: true,
                    disabled: true,
                    handler: function(){
                        var form = this.up('form').getForm();
                        var sender = form.findField("sender").getValue();
                        var domain = form.findField("domain").getValue();
                        var source = form.findField("source").getValue();
                        this.up("window").callbackDeleteEntries(sender, domain, source);
                        this.up('window').destroy();
                    }
                }
            ]
        }
    ]

});