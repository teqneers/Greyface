Ext.define("Greyface.view.blacklist.domain.AddDomainWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_blacklistAddDomainWindow",
    modal:true,
    title: "Add Email to Whitelist",
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
                    fieldLabel: 'Domain',
                    name: 'domain',
                    allowBlank: false
                }
            ],
            buttons: [
                {
                    text: 'Add',
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