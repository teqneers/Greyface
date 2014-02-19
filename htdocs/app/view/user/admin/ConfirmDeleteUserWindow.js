Ext.define("Greyface.view.user.admin.ConfirmDeleteUserWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_ConfirmDeleteUserWindow",
    width:400,
    modal:true,
    title: Greyface.tools.Dictionary.translate("confirmDeleteUser"),
    resizable:false,
    layout:"fit",
    config: {
        userRecord:'',
        test:''
    },
    constructor: function(cfg) {
        this.callParent(arguments);
    },
    items: [
        {
            xtype: 'form',
            bodyPadding: 10,
            border:false,
            defaultType: 'text',
            items: [
                {
//                    text: Greyface.tools.Dictionary.translate("confirmDeleteUserDescription") + '\n'+this.userRecord.get('username')
                    text: Greyface.tools.Dictionary.translate("confirmDeleteUserDescription") + '\n'+ this.up("window").test
                }
            ],
            buttons: [
                {
                    text: Greyface.tools.Dictionary.translate("delete"),
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
                        this.up("window").callbackDeleteUser(username, email, password, isAdmin, randomizePassword, sendEmail);
                        this.up('window').destroy();
                    }
                },
                {
                    text: Greyface.tools.Dictionary.translate("delete"),
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