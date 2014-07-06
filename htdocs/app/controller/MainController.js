Ext.define("Greyface.controller.MainController", {
    requires: [],
    extend: "Ext.app.Controller",
    refs: [
        { ref: "languageSelector", selector: "splitbutton[actionId=languageSelectorMain]" }
    ],
    views: ["MainScreen"],

    init: function () {
        this.control({
            "button[actionId=greyListButton]": {
                click: function(){
                    this.toggleContentScreen(0);
                }

            },



            "button[actionId=autoWhiteListSplitButton]": {
                click: function(cmp){
                    cmp.showMenu()
                }
            },
            "menuitem[actionId=autoWhiteListEmailMenuButton]": {
                click: function(){
                    this.toggleContentScreen(1);
                }
            },
            "menuitem[actionId=autoWhiteListDomainMenuButton]": {
                click: function(){
                    this.toggleContentScreen(2);
                }
            },



            "button[actionId=whiteListSplitButton]": {
                click: function(cmp){
                    cmp.showMenu()
                }
            },
            "menuitem[actionId=whiteListEmailMenuButton]": {
                click: function(){
                    this.toggleContentScreen(3);
                }
            },
            "menuitem[actionId=whiteListDomainMenuButton]": {
                click: function(){
                    this.toggleContentScreen(4);
                }
            },



            "button[actionId=blacklistSplitButton]": {
                click: function(cmp){
                    cmp.showMenu()
                }
            },
            "menuitem[actionId=blacklistEmailMenuButton]": {
                click: function(){
                    this.toggleContentScreen(5);
                }
            },
            "menuitem[actionId=blacklistDomainMenuButton]": {
                click: function(){
                    this.toggleContentScreen(6);
                }
            },



            "button[actionId=userSplitButton]": {
                click: function(cmp){
                    cmp.showMenu()
                }
            },
            "menuitem[actionId=userAdminMenuButton]": {
                click: function(){
                    this.toggleContentScreen(7);
                }
            },
            "menuitem[actionId=userAliasMenuButton]": {
                click: function(){
                    this.toggleContentScreen(8);
                }
            },

            // Logout button
            "button[actionId=logoutButton]": {
                click: function(){
                    this.logoutFromServer();
                }
            },

            // Change password
            "menuitem[actionId=changePasswordButton]": {
                click: function() {
                    var userRecord = Greyface.tools.User.getUserRecord();
                    console.log(userRecord)
                    Ext.create("Greyface.view.user.admin.SetUserPasswordWindow", {userRecord:userRecord}).show();
                }

            },

            // Language selector
            "menu[actionId=languageSelectorMenuMain]": {
                click: function(menu, item, e, eOpts) {
                    this.getLanguageSelector().setText(item.text);
                    this.getLanguageSelector().setIcon(item.icon);
                    document.cookie = 'preferredLanguageKey='+item.languageKey;
                    location.replace("index.php?language="+item.languageKey);
                }
            }
        });
    },

    toggleContentScreen: function(cardNr){
        var panel = Ext.ComponentQuery.query("panel[actionId=contentPanel]")[0];
        panel.getLayout().setActiveItem(cardNr);
        var layout = panel.getLayout().getActiveItem();
        layout.down('gridpanel').getStore().reload();
    },

    showLoginScreen: function(){
        this.application.viewport.getLayout().setActiveItem(0);
        // @TODO logik zum logout fehlt.
        // Der user muss serverseitig ausgelogt werden, das anmeldecookie muss gelöscht werden! Die session muss zerstört werden.
    },



    // Logout
    logoutFromServer: function() {
        Ext.Ajax.request({
//            scope: this,
            url: "api/logout.php",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse)
                location.reload();
            },
            failure: function(response, opts) {
                // nothing... no connection
            }
        });
    }
});