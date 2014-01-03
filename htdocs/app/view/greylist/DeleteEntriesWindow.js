Ext.define("Greyface.view.greylist.DeleteEntriesWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_greylistDeleteEntriesWindow",
    modal:true,
//    actionId:"",
    title: "Delete Entries by Date",
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
                    text:'All entrys from the past to the selected date will be deleted!'
                },
                {
                    xtype:"datefield",
                    actionId:"greylistDeleteToDate",
                    name:"to_date",
                    fieldLabel: 'To',
                    anchor: "100%",
                    padding:"10 20 20 20",
                    value: new Date()
                }
            ]

        }
    ],
    buttons: [
        {
            text: 'Delete',
            handler: function(){
                var datefield = Ext.ComponentQuery.query("datefield[actionId=greylistDeleteToDate]")[0];
                var value = datefield.getValue();
                this.up("window").callbackDeleteEntries(value);
                this.up('panel').destroy();
            }
    }]
});