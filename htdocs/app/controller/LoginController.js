Ext.define("Greyface.controller.LoginController", {
    extend: "Ext.app.Controller",
    refs: [
        { ref: "userName", selector: "gf_login textfield[actionId=usernametext]" },
        { ref: "password", selector: "gf_login textfield[actionId=passwordtext]" },
        { ref: "loginForm", selector: "panel[actionId=loginForm]" }
    ],
    views: ["LoginScreen"],

    config:{
        isMainCreated:false
    },

    init: function () {
        this.control({
            // Login button
            "gf_login button[actionId=loginButton]": {
                click: this.login
            }
        })
    },
    onLaunch: function() {
        // tries to authenticate on its own
        var ret = this.authenticate();
    },



    // Login
    login: function() {
        var loginForm = this.getLoginForm().up("form");
        if (loginForm.isValid()) {
            loginForm.submit({
                success: function(form, action) {
                    console.log('Success',  action.result.msg);
                    this.createMainScreen();
                    this.showMainScreen();
                },
                failure: function(form, action) {
                    Ext.Msg.alert('Failed',  action.result.msg);
                },
                scope: this
            });
        }
    },
    authenticate: function() {
        Ext.Ajax.request({
            url: "api/login.php",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse)
                if(decResponse.success == true) {
                    this.createMainScreen();
                    this.showMainScreen();
                } else {
                    this.showLoginScreen();
                }
            },
            failure: function(response, opts) {
                this.showLoginScreen();
            },
            scope: this
        });
    },



    // Create screens on startup...
    createMainScreen: function() {
        if(!this.getIsMainCreated()) {
            this.application.viewport.add({xtype: "gf_main"});
            this.setIsMainCreated(true);
        }
    },
    showMainScreen: function() {
        if(this.getIsMainCreated()) {
            this.application.viewport.getLayout().setActiveItem(1);
        }
    },
    showLoginScreen: function() {
        this.application.viewport.getLayout().setActiveItem(0);
        this.getLoginForm().show();
    }

});