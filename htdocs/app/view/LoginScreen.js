﻿Ext.define("Greyface.view.LoginScreen", {
    extend: "Ext.form.Panel",
    xtype: "gf_login",

    url: "api/login.php",

    layout: {
        type : "vbox",
        pack : "center",
        align : "center"
    },
    border:false,
    items: [
        {
            xtype: "panel",
            hidden:true,
            actionId: "loginForm",
            title:Greyface.tools.Dictionary.translate("login"),
            border: true,
            shadow:"drop",
            shadowOffset:5,
            width:350,
            layout: {
                type:"vbox",
                align:"right"
            },
            items:[
                {
                    xtype: "textfield",
                    name:"username",
                    actionId: "usernametext",
                    fieldLabel: Greyface.tools.Dictionary.translate("username"),
                    value:"admin",
                    margin:"10 10 10 10",
                    allowBlank:false
                },
                {
                    xtype: "textfield",
                    name: "password",
                    actionId: "passwordtext",
                    fieldLabel: Greyface.tools.Dictionary.translate("password"),
                    inputType: "password",
                    value:"admin",
                    margin:"10 10 0 10"
                },
                {
                    xtype: "checkbox",
                    name:"rememberLogin",
                    actionId: "rememberLogin",
                    fieldLabel: Greyface.tools.Dictionary.translate("rememberMe"),
                    checked:false,
                    margin:"10 10 20 10"
                }
            ],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    items: [
                        "->",
                        {
                            xtype: "splitbutton",
                            text: Greyface.tools.Dictionary.getLanguageName(),
                            actionId: "languageSelector",
                            icon: "resources/language/"+Greyface.tools.Dictionary.getLanguage()+".png",
                            menu: new Ext.menu.Menu({
                                actionId: "languageSelectorMenu",
                                items: Greyface.tools.Dictionary.getLanguageItems()
                            }),
                            listeners: {
                                click: function(cmp){
                                    cmp.showMenu()
                                }
                            }
                        },
                        {
                            xtype: "button",
                            text: Greyface.tools.Dictionary.translate("login"),
                            actionId: "loginButton",
                            icon: "resources/images/door_in.png",
                            disabled:true,
                            formBind:true,
                            align:"right"
                        }
                    ]
                }
            ]
        }
    ]
});