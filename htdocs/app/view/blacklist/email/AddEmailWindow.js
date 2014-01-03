Ext.define("Greyface.view.blacklist.email.AddEmailWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_blacklistAddEmailWindow",
    modal:true,
    title: "Add Email to Blacklist",
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