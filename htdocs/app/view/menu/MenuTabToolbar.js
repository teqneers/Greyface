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
                    // these will render as dropdown menu items when the arrow is clicked:
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
        {
            xtype:"combo",
            actionId:"comboBoxLanguage",
            multiselect:false,
            editable: false,
            typeAhead:false,
            //store:languageStore,      // wire-up with the STORE
            fieldLabel:Greyface.tools.Dictionary.translate("language"),      // Label that decorates the combo-box besides (left)
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