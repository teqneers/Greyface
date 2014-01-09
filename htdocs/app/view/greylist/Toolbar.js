Ext.define("Greyface.view.greylist.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_greylistToolbar",
    actionId:"greylistToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"greylistToolbarDeleteEntriesByTime",
            text: Greyface.tools.Dictionary.translate("deleteEntriesByTime"),
            icon: "resources/images/clock_delete.png"
        },
        {
            xtype:"combo",
            actionId:"greylistToolbarFilterBy",
            multiselect:false,
            editable: false,
            typeAhead:false,
            fieldLabel:Greyface.tools.Dictionary.translate("filterBy"),
            labelAlign:"right",
            displayField: "username",
            valueField: "user_id",
            forceSelection: true
        },
        {
            xtype:"textfield",
            actionId:"greylistToolbarFulltext",
            name:"greylistToolbarFulltext",
            emptyText:"search value...",
            fieldLabel:Greyface.tools.Dictionary.translate("fulltextSearch"),
            labelAlign:"right",
            enableKeyEvents:true
        },
        {
            xtype:"button",
            actionId:"greylistToolbarCancelFilter",
            text:"",
            icon: "resources/images/cross_red.png",
            hidden:true
        }
    ]
});