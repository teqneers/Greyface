Ext.define("Greyface.view.user.admin.SetUserPasswordWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_SetUserPasswordWindow",
    modal:true,
    title: "Set new user password",
    resizable:false,
    layout:"fit",
    config: {
        userRecord:""
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
                    fieldLabel: 'Password',
                    name: 'password',
                    allowBlank: false,
                    inputType: 'password'
                },
                {
                    fieldLabel: 'Retype password',
                    name: 'retypePassword',
                    allowBlank: false,
                    inputType: 'password',
                    validator: function(retypedPassword){
                        var form = this.up('form').getForm();
                        var password = form.findField("password").getValue();
                        if (retypedPassword === password) {
                            return true;
                        } else {
                            return "The retyped password differs to the password"
                        }
                    }
                }
            ],
            buttons: [
                {
                    text: 'Set',
                    formBind: true,
                    disabled: true,
                    handler: function(){
                        var form = this.up('form').getForm();
                        var password = form.findField("password").getValue();
                        this.up("window").userRecord.setPassword(password);
                        this.up('window').destroy();
                    }
                }
            ]
        }
    ]

});