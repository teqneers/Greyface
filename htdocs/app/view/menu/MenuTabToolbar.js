Ext.define("Greyface.view.menu.MenuTabToolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype: "gf_menuTabToolbar",
    actionId:"menuTabToolbar",
    //layout: "auto",
    border:false,
    items: [
        {
            xtype: 'button',
            text: 'Greylist',
            actionId:"greyListButton",
            icon: "resources/images/page_grey.png"
        },
        {
            xtype: 'splitbutton',
            text : 'Auto whitelist',
            actionId: "autoWhiteListSplitButton",
            icon: "resources/images/page_autowhite.png",
            menu: new Ext.menu.Menu({
                items: [
                    {text: 'Email list', actionId: "autoWhiteListEmailMenuButton"},
                    {text: 'Domain  list', actionId: "autoWhiteListDomainMenuButton"}
                ]
            })
        },
        {
            xtype: 'splitbutton',
            text: 'Whitelist',
            actionId: "whiteListSplitButton",
            icon: "resources/images/page_white.png",
            menu: new Ext.menu.Menu({
                items: [
                    {text: 'Email list', actionId: "whiteListEmailMenuButton"},
                    {text: 'Domain  list', actionId: "whiteListDomainMenuButton"}
                ]
            })
        },
        {
            xtype: 'splitbutton',
            text: 'Blacklist',
            actionId: "blacklistSplitButton",
            icon: "resources/images/page_black.png",
            menu: new Ext.menu.Menu({
                items: [
                    // these will render as dropdown menu items when the arrow is clicked:
                    {text: 'Email list', actionId: "blacklistEmailMenuButton"},
                    {text: 'Domain  list', actionId: "blacklistDomainMenuButton"}
                ]
            })
        },
        {
            xtype: 'splitbutton',
            text: 'User',
            actionId: "userSplitButton",
            icon: "resources/images/user_go.png",
            menu: new Ext.menu.Menu({
                items: [
                    {text: 'User', actionId: "userAdminMenuButton"},
                    {text: 'Aliases', actionId: "userAliasMenuButton"},
                    {text: 'Change Password', actionId: "userChangePasswordMenuButton"}
                ]
            })
        },
        {
            xtype: 'button',
            text: 'Logout',
            actionId: "logoutButton",
            icon: "resources/images/door_in.png"
        },
        '->',
        {
            xtype:"combo",
            actionId:"comboBoxLanguage",
            multiselect:false,
            editable: false,
            typeAhead:false,
            //store:languageStore,      // wire-up with the STORE
            fieldLabel:"Language",      // Label that decorates the combo-box besides (left)
            labelAlign:"right",
            displayField:"language",    // the value that is shown
            valueField:"key",           // the underlying value of the item
            queryMode: "local",
            queryCaching:true,
            enableKeyEvents:false,
            listeners:{
                change: function(combo, newValue, oldValue){
                    console.log("CHANGE");
                    console.log("Old Value: " + oldValue);
                    console.log("New Value: " + newValue);
                },
                select: function(combo, records, eOpts){
                    console.log("SELECT");
                    console.log(records[0].get("language"));
                },
                expand: function(field, eOpts){
                    console.log("EXPAND");
                    console.log(field);
                    field.reset();
                },
                dirtychange: function(combo, isDirty, eOpts ){
                    console.log("DIRTYCHANGE");
                }
            }
        }
    ]
});