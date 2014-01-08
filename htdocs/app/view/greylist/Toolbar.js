Ext.define("Greyface.view.greylist.Toolbar", {
    extend:"Ext.toolbar.Toolbar",
    xtype:"gf_greylistToolbar",
    actionId:"greylistToolbar",
    border:false,
    items: [
        {
            xtype:"button",
            actionId:"greylistToolbarDeleteEntriesByTime",
            text:"Delete entries by time",
            icon: "resources/images/clock_delete.png"
        },
        {
            xtype:"combo",
            actionId:"greylistToolbarFilterBy",
            multiselect:false,
            editable: false,
            typeAhead:false,
            fieldLabel:"Filter by:",
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
            fieldLabel:"Fulltext:",
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