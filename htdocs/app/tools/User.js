Ext.define("Greyface.tools.User",{
    singleton:true,
    config: {
        username:"",
        isAdmin:false,
        email:"",
        userId:"",
        mutableUiElements:""
    },
    setUser: function(cfg) {
        this.setConfig(cfg);
    },
    showUserUi: function() {
        this.getMutableUiElements().forEach(function(element){
            element.setVisible(false);
        });
    },
    showAdminUi: function() {
        this.getMutableUiElements().forEach(function(element){
            element.setVisible(true);
        });
    },
    configureUserDependentLayout: function() {

        if(this.getMutableUiElements() == "") {
            var mutableUiComponents = new Array();

            // Toolbar items
            mutableUiComponents.push( Ext.ComponentQuery.query("splitbutton[actionId=autoWhiteListSplitButton]")[0] );
            mutableUiComponents.push( Ext.ComponentQuery.query("splitbutton[actionId=whiteListSplitButton]")[0] );
            mutableUiComponents.push( Ext.ComponentQuery.query("splitbutton[actionId=blacklistSplitButton]")[0] );
            mutableUiComponents.push( Ext.ComponentQuery.query("splitbutton[actionId=userSplitButton]")[0] );
            mutableUiComponents.push( Ext.ComponentQuery.query("splitbutton[actionId=userSplitButton]")[0] );

            // Items in Greylist category
            mutableUiComponents.push( Ext.ComponentQuery.query("button[actionId=greylistToolbarDeleteEntriesByTime]")[0] );
            mutableUiComponents.push( Ext.ComponentQuery.query("combo[actionId=greylistToolbarFilterBy]")[0] );

            this.setMutableUiElements(mutableUiComponents);
        }
        if(this.getIsAdmin()) {
            this.showAdminUi();
        } else {
            this.showUserUi();
        }
    }
});