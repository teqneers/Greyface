Ext.define("Greyface.view.autowhitelist.email.AddEmailWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_autowhitelistAddEmailWindow",
    modal:true,
    title: "Add Email to Auto whitelist",
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
                    fieldLabel: 'Sender',
                    name: 'sender',
                    allowBlank: false
                },
                {
                    fieldLabel: 'Domain',
                    name: 'domain',
                    allowBlank: false
                },
                {
                    fieldLabel: 'Source',
                    name: 'source',
                    allowBlank: false,
                    regex: /([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4}|(\d{1,3}\.){3}\d{1,3}/,
                    regexText: "Should be a valid IPv4/6 address"
                }
            ],
            buttons: [
                {
                    text: 'Add',
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