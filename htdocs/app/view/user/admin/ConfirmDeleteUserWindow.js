Ext.define("Greyface.view.user.admin.ConfirmDeleteUserWindow",{
    extend:"Ext.window.Window",
    xtype:"gf_ConfirmDeleteUserWindow",
    width:400,
    modal:true,
    title: Greyface.tools.Dictionary.translate("confirmDeleteUser"),
    resizable:false,
    layout:"fit",
    config: {
        userRecord:'',
        store:''
    },
    items: [
        {
            xtype: 'form',
            bodyPadding: 10,
            border:false,
            defaultType: 'text',
            items: [
                {
                    text:'',
                    actionId: 'userToDelete'
                }
            ],
            buttons: [
                {
                    text: Greyface.tools.Dictionary.translate("delete"),
                    handler: function(){
                        this.up("window").userRecord.deleteItem();
                        this.up("window").store.reload();
                        this.up('window').destroy();
                    }
                },
                {
                    text: Greyface.tools.Dictionary.translate("cancel"),
                    handler: function(){
                        this.up('window').destroy();
                    }
                }
            ]
        }
    ],
    listeners: {
        beforerender: function(window, layout) {
            window.down('text[actionId=userToDelete]').setText(Greyface.tools.Dictionary.translate("confirmDeleteUserDescription")+'\n'+this.getUserRecord().data.username);
        }
    }
});