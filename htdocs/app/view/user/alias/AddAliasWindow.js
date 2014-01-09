Ext.define("Greyface.view.user.alias.AddAliasWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_AddUserWindow",
    modal:true,
    title: Greyface.tools.Dictionary.translate("addAlias"),
    resizable:false,
    layout:"fit",
    config: {
        callbackAddAlias:""
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
                    fieldLabel: Greyface.tools.Dictionary.translate("alias"),
                    name: 'alias',
                    allowBlank: false,
                    vtype:'email'
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
                        var alias = form.findField("alias").getValue();
                        this.up("window").callbackAddAlias(username, alias);
                        this.up('window').destroy();
                    }
                }
            ]
        }
    ]

});