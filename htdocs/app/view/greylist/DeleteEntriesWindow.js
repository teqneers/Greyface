Ext.define("Greyface.view.greylist.DeleteEntriesWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_greylistDeleteEntriesWindow",
    modal:true,
//    actionId:"",
    title: Greyface.tools.Dictionary.translate("deleteEntriesByTime"),
    resizable:false,
    layout:"fit",
    config: {
        callbackDeleteEntries:""
    },
    constructor: function(cfg) {
        this.callParent(arguments);
    },
    items: [
        {
            xtype: 'panel',
            bodyPadding: 10,
            border:false,
            layout: 'auto',
            items: [
                {
                    xtype:"text",
                    anchor: "100%",
                    padding:"20 20 10 20",
                    text:Greyface.tools.Dictionary.translate("deleteEntriesByTimeDescription")
                },
                {
                    xtype:"datefield",
                    actionId:"greylistDeleteToDate",
                    name:"to_date",
                    fieldLabel: Greyface.tools.Dictionary.translate("to:"),
                    anchor: "100%",
                    padding:"10 20 20 20",
                    value: new Date()
                }
            ]

        }
    ],
    buttons: [
        {
            text:Greyface.tools.Dictionary.translate("delete"),
            handler: function(){
                var datefield = Ext.ComponentQuery.query("datefield[actionId=greylistDeleteToDate]")[0];
                var value = datefield.getValue();
                this.up("window").callbackDeleteEntries(value);
                this.up('panel').destroy();
            }
    }]
});