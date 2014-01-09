Ext.define("Greyface.view.user.admin.SetUserPasswordWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_SetUserPasswordWindow",
    modal:true,
    title: Greyface.tools.Dictionary.translate("setNewUserPassword"),
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
                    fieldLabel: Greyface.tools.Dictionary.translate("password"),
                    name: 'password',
                    allowBlank: false,
                    inputType: 'password'
                },
                {
                    fieldLabel: Greyface.tools.Dictionary.translate("retypePassword"),
                    name: 'retypePassword',
                    allowBlank: false,
                    inputType: 'password',
                    validator: function(retypedPassword){
                        var form = this.up('form').getForm();
                        var password = form.findField("password").getValue();
                        if (retypedPassword === password) {
                            return true;
                        } else {
                            return Greyface.tools.Dictionary.translate("retypePasswordError")
                        }
                    }
                }
            ],
            buttons: [
                {
                    text: Greyface.tools.Dictionary.translate("set"),
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