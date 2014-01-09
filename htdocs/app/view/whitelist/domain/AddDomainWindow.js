Ext.define("Greyface.view.whitelist.domain.AddDomainWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_whitelistAddDomainWindow",
    modal:true,
    title: Greyface.tools.Dictionary.translate("whitelist") + ": " + Greyface.tools.Dictionary.translate("addDomain"),
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
                    fieldLabel: Greyface.tools.Dictionary.translate("domain"),
                    name: 'domain',
                    allowBlank: false
                }
            ],
            buttons: [
                {
                    text: Greyface.tools.Dictionary.translate("add"),
                    formBind: true,
                    disabled: true,
                    handler: function(){
                        var form = this.up('form').getForm();
                        var domain = form.findField("domain").getValue();
                        this.up("window").callbackDeleteEntries(domain);
                        this.up('window').destroy();
                    }
                }
            ]
        }
    ]

});