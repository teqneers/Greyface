Ext.define("Greyface.view.MainScreen", {
    extend: "Ext.panel.Panel",
    requires:["Greyface.view.menu.MenuTabToolbar"],
    xtype: "gf_main",
    actionId:"mainScreen",
    layout: "border",
    border:false,
    items: [
        {
            xtype: 'panel',
            region:	"north",
            border:	false,
            height: 55,
            width:220,
            html: "<img src='resources/images/greyface.jpg' height='55' width='220'>"
        },{
            xtype: 'panel',
            region:	"north",
            border: false,
            layout: "auto",
            items: [
                {xtype:"gf_menuTabToolbar"}
            ]
        },
        {
            xtype: 'panel',
            actionId:"contentPanel",
            region:	"center",
            border: false,
            layout: "card",
            items:[
                {
                    xtype:"panel",
                    actionId:"greylistPanel",
                    border:false,
                    layout:"fit",
                    tbar: [
                        {xtype:"gf_greylistToolbar"}
                    ],
                    items:[
                        {xtype:"gf_greylistPanel"}
                    ]
                },

                {
                    xtype:"panel",
                    actionId:"autoWhitelistEmailPanel",
                    border:false,
                    layout:"fit",
                    tbar:[
                        {xtype:"gf_autoWhitelistEmailToolbar"}
                    ],
                    items:[
                        {xtype:"gf_autoWhitelistEmailPanel"}
                    ]
                },
                {
                    xtype:"panel",
                    actionId:"autoWhitelistDomainPanel",
                    border:false,
                    layout:"fit",
                    tbar:[
                        {xtype:"gf_autoWhitelistDomainToolbar"}
                    ],
                    items:[
                        {xtype:"gf_autoWhitelistDomainPanel"}
                    ]
                },

                {
                    xtype:"panel",
                    actionId:"whitelistEmailPanel",
                    border:false,
                    layout:"fit",
                    tbar:[
                        {xtype:"gf_whitelistEmailToolbar"}
                    ],
                    items:[
                        {xtype:"gf_whitelistEmailPanel"}
                    ]
                },
                {
                    xtype:"panel",
                    actionId:"whitelistDomainPanel",
                    border:false,
                    layout:"fit",
                    tbar:[
                        {xtype:"gf_whitelistDomainToolbar"}
                    ],
                    items:[
                        {xtype:"gf_whitelistDomainPanel"}
                    ]
                },

                {
                    xtype:"panel",
                    actionId:"blacklistEmailPanel",
                    border:false,
                    layout:"fit",
                    tbar:[
                        {xtype:"gf_blacklistEmailToolbar"}
                    ],
                    items:[
                        {xtype:"gf_blacklistEmailPanel"}
                    ]
                },
                {
                    xtype:"panel",
                    actionId:"blacklistDomainPanel",
                    border:false,
                    layout:"fit",
                    tbar:[
                        {xtype:"gf_blacklistDomainToolbar"}
                    ],
                    items:[
                        {xtype:"gf_blacklistDomainPanel"}
                    ]
                },

                {
                    xtype:"panel",
                    actionId:"userAdminPanel",
                    border:false,
                    layout:"fit",
                    tbar:[
                        {xtype:"gf_userAdminToolbar"}
                    ],
                    items:[
                        {xtype:"gf_userAdminPanel"}
                    ]
                },
                {
                    xtype:"panel",
                    actionId:"userAliasesPanel",
                    border:false,
                    layout:"fit",
                    tbar:[
                        {xtype:"gf_userAliasToolbar"}
                    ],
                    items:[
                        {xtype:"gf_userAliasPanel"}
                    ]
                }
            ]
        },
        {
            xtype: 'panel',
            region: "south",
            html:"<center>Greyface by TEQneers GmbH & Co. KG</center>",
            height: 25
        }
    ]

});