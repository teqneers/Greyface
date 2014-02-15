Ext.define("Greyface.view.user.alias.AddAliasWindow",{
    extend:"Ext.window.Window",
    requires: ['Greyface.view.user.alias.AliasTextfieldCancel'],
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
            aliases:1,
            actionId:'addAliasForm',
            bodyPadding: 10,
            border:false,
            defaultType: 'textfield',
            items: [
                {
                    xtype:'combobox',
                    store: Ext.create("Greyface.store.UserListStore"),
                    displayField:'username',
                    actionId:'userselect',
                    fieldLabel: Greyface.tools.Dictionary.translate("username"),
                    name: 'username',
                    allowBlank: false,
                    typeAhead:true,
                    typeAheadDelay:100,
                    multiselect:false,
                    queryCaching:false,
                    minChars:1,
                    forceSelection:true
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
                    xtype: 'button',
                    actionId:'addAnotherAlias',
                    text:Greyface.tools.Dictionary.translate("addAnotherAlias"),
                    icon: "resources/images/user_add.png",
                    handler:function() {
                        var form = this.up('[actionId=addAliasForm]');
                        form.add(
                            {
                                xtype: 'AliasfieldCancel',
                                fieldLabel: Greyface.tools.Dictionary.translate("alias"),
                                name: 'alias'+form.aliases
                            }
                        )
                        form.aliases++;
                    }
                },
                {
                    text: Greyface.tools.Dictionary.translate("add"),
                    formBind: true,
                    disabled: true,
                    handler: function(){
                        var form = this.up('form').getForm();
                        var username = form.findField("username").getValue();
                        var alias = form.findField("alias").getValue();
                        for (var i=1; i < this.up('[actionId=addAliasForm]').aliases; i++) {
                            if (this.up('[actionId=addAliasForm]').down('[name=alias'+i+']') !== null) {
                                alias += '#' + this.up('[actionId=addAliasForm]').down('[name=alias'+i+']').getValue();
                            }
                        };
                        this.up("window").callbackAddAlias(username, alias);
                        this.up('window').destroy();
                    }
                }
            ]
        }
    ]

});