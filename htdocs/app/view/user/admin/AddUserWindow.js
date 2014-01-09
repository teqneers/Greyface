Ext.define("Greyface.view.user.admin.AddUserWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_AddUserWindow",
    modal:true,
    title: Greyface.tools.Dictionary.translate("createUser"),
    resizable:false,
    layout:"fit",
    config: {
        callbackAddUser:""
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
                    fieldLabel: Greyface.tools.Dictionary.translate("username"),
                    name: 'username',
                    allowBlank: false
                },
                {
                    fieldLabel: Greyface.tools.Dictionary.translate("email"),
                    name: 'email',
                    allowBlank: false,
                    vtype:'email'
                },
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
                            return Greyface.tools.Dictionary.translate("retypePasswordError");
                        }
                    }
                },
                {
                    xtype:'checkboxfield',
                    fieldLabel: Greyface.tools.Dictionary.translate("statusAdmin"),
                    name: 'isAdmin',
                    checked   : false
                },
                {
                    xtype:'checkboxfield',
                    fieldLabel: Greyface.tools.Dictionary.translate("createRandomPasswort"),
                    name: 'randomizePassword',
                    checked   : false,
                    listeners: {
                        change: function(checkBox, newValue, oldValue) {
                            var form = this.up('form').getForm();
                            var passwordField = form.findField("password");
                            var retypePasswordField = form.findField("retypePassword");
                            var sendEmailCheckbox = form.findField("sendEmail");

                           if (newValue) {
                               passwordField.setValue("");
                               retypePasswordField.setValue("");
                               retypePasswordField.setDisabled(true);
                               passwordField.setDisabled(true);
                               retypePasswordField.setDisabled(true);
                               sendEmailCheckbox.setValue(true);
                           } else {
                           	   passwordField.setDisabled(false);
                               retypePasswordField.setDisabled(false);
                           }
                        }
                    }
                },
                {
                    xtype:'checkboxfield',
                    fieldLabel: Greyface.tools.Dictionary.translate("sendEmail"),
                    name: 'sendEmail',
                    checked   : false
                }
            ],
            buttons: [
                {
                    text: Greyface.tools.Dictionary.translate("add"),
                    formBind: true,
                    disabled: true,
                    handler: function(){
                        var form = this.up('form').getForm();
                        var username = form.findField("username").getValue();
                        var email = form.findField("email").getValue();
                        var password = form.findField("password").getValue();
                        var isAdmin = form.findField("isAdmin").getValue();
                        var randomizePassword = form.findField("randomizePassword").getValue();
                        var sendEmail = form.findField("sendEmail").getValue();
                        this.up("window").callbackAddUser(username, email, password, isAdmin, randomizePassword, sendEmail);
                        this.up('window').destroy();
                    }
                }
            ]
        }
    ]

});