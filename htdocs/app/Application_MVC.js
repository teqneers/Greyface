Ext.application({
    name: "Greyface",
    appFolder: "app",
    requires: [
        "Greyface.tools.Registry"
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
