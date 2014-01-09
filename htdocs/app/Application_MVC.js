Ext.application({
    name: "Greyface",
    appFolder: "app",
    requires: [
        "Greyface.tools.Registry",
        "Greyface.tools.Dictionary"
    ],
    controllers: [
        "LoginController",
        "MainController",
        "GreylistController",
        "AutoWhitelistController",
        "WhitelistController",
        "BlacklistController",
        "UserController"
    ],
    launch: function() {
        this.viewport = Ext.create("Ext.container.Viewport", {
            renderTo: Ext.getBody(),
            layout: "card",
            items: [
                {
                    xtype: "gf_login"
                }
            ]
        });
    }
});
