Ext.define("Greyface.view.user.admin.AddUserWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_AddUserWindow",
    modal:true,
    title: "Create new user",
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
                    fieldLabel: 'Username',
                    name: 'username',
                    allowBlank: false
                },
                {
                    fieldLabel: 'Email',
                    name: 'email',
                    allowBlank: false,
                    vtype:'email'
                },
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
                },
                {
                    xtype:'checkboxfield',
                    fieldLabel: 'Admin',
                    name: 'isAdmin',
                    checked   : false
                },
                {
                    xtype:'checkboxfield',
                    fieldLabel: 'Create random password',
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
                    fieldLabel: 'Send email',
                    name: 'sendEmail',
                    checked   : false
                }
            ],
            buttons: [
                {
                    text: 'Add',
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
                        this.up("window").callbackDeleteEntries(username, email, password, isAdmin, randomizePassword, sendEmail);
                        this.up('window').destroy();
                    }
                }
            ]
        }
    ]

});