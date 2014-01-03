Ext.define("Greyface.view.whitelist.email.AddEmailWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_whitelistAddEmailWindow",
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
                    fieldLabel: 'Email',
                    name: 'email',
                    allowBlank: false,
                    vtype: 'email'
                }
            ],
            buttons: [
                {
                    text: 'Add',
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