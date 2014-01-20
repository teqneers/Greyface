Ext.define("Greyface.view.menu.MenuTabToolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype: "gf_menuTabToolbar",
    actionId:"menuTabToolbar",
    //layout: "auto",
    border:false,
    items: [
        {
            xtype: 'button',
            text: Greyface.tools.Dictionary.translate("greylist"),
            actionId:"greyListButton",
            icon: "resources/images/page_grey.png"
        },
        {
            xtype: 'splitbutton',
            text : Greyface.tools.Dictionary.translate("autoWhitelist"),
            actionId: "autoWhiteListSplitButton",
            icon: "resources/images/page_autowhite.png",
            menu: new Ext.menu.Menu({
                items: [
                    {text:  Greyface.tools.Dictionary.translate("emailList"), actionId: "autoWhiteListEmailMenuButton"},
                    {text:  Greyface.tools.Dictionary.translate("domainList"), actionId: "autoWhiteListDomainMenuButton"}
                ]
            })
        },
        {
            xtype: 'splitbutton',
            text: Greyface.tools.Dictionary.translate("whitelist"),
            actionId: "whiteListSplitButton",
            icon: "resources/images/page_white.png",
            menu: new Ext.menu.Menu({
                items: [
                    {text:  Greyface.tools.Dictionary.translate("emailList"), actionId: "whiteListEmailMenuButton"},
                    {text:  Greyface.tools.Dictionary.translate("domainList"), actionId: "whiteListDomainMenuButton"}
                ]
            })
        },
        {
            xtype: 'splitbutton',
            text: Greyface.tools.Dictionary.translate("blacklist"),
            actionId: "blacklistSplitButton",
            icon: "resources/images/page_black.png",
            menu: new Ext.menu.Menu({
                items: [
                    {text:  Greyface.tools.Dictionary.translate("emailList"), actionId: "blacklistEmailMenuButton"},
                    {text:  Greyface.tools.Dictionary.translate("domainList"), actionId: "blacklistDomainMenuButton"}
                ]
            })
        },
        {
            xtype: 'splitbutton',
            text: Greyface.tools.Dictionary.translate("user"),
            actionId: "userSplitButton",
            icon: "resources/images/user_go.png",
            menu: new Ext.menu.Menu({
                items: [
                    {text:  Greyface.tools.Dictionary.translate("user"), actionId: "userAdminMenuButton"},
                    {text:  Greyface.tools.Dictionary.translate("alias"), actionId: "userAliasMenuButton"}
                ]
            })
        },
        {
            xtype: 'button',
            text: Greyface.tools.Dictionary.translate("logout"),
            actionId: "logoutButton",
            icon: "resources/images/door_in.png"
        },
        '->',
        'tbseparator',
        {
            xtype:'splitbutton',
            actionId: 'userProfile',
            icon: "resources/images/user_edit.png"
        },
        {
            xtype: "splitbutton",
            text: Greyface.tools.Dictionary.getLanguageName(),
            actionId: "languageSelectorMain",
            icon: "resources/language/"+Greyface.tools.Dictionary.getLanguage()+".png",
            menu: new Ext.menu.Menu({
                actionId: "languageSelectorMenuMain",
                items: Greyface.tools.Dictionary.getLanguageItems()
            }),
            listeners: {
                click: function(cmp){
                    cmp.showMenu()
                }
            }
        }
    ]
});