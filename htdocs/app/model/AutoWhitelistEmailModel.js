Ext.define("Greyface.model.AutoWhitelistEmailModel",{
    extend:"Ext.data.Model",
    fields:[
        "sender_name",
        "sender_domain",
        "src",
        {
            name: "first_seen",
            type: "date",
            dateReadFormat: "Y-m-d H:i:s",
            dateWriteFormat: "timestamp",
            persist:false
        },
        {
            name: "last_seen",
            type: "date",
            dateReadFormat: "Y-m-d H:i:s",
            dateWriteFormat: "timestamp",
            persist:false
        },
        {
            name: "dynamicId",
            convert: function(value, record) {
                return {sender_name:record.data.sender_name, sender_domain: record.data.sender_domain, src: record.data.src} ;
            },
            serialize: function(value, record) {
                return {sender_name: record.oldSender_name, sender_domain: record.oldSender_domain, src: record.oldSrc};
            }
        }
    ],
    oldSender_name:"",
    oldSender_domain:"",
    oldSrc:"",

    set: function(value) {
        console.log(value)
        if(value.sender_name !== undefined) {
            console.log(value.sender_name);
            this.oldSender_name = this.get("sender_name");
        }
        if(value.sender_domain !== undefined) {
            console.log(value.sender_domain);
            this.oldSender_domain = this.get("sender_domain");
        }
        if(value.src !== undefined) {
            console.log(value.src);
            this.oldSrc = this.get("src");
        }

        this.callParent(arguments);
    },

    deleteItem: function() {
        Ext.Ajax.request({
            url: "api/CRUDRouter.php?action=delete",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse.data[0]);
            },
            failure: function(response, opts) {
            },
            method: "POST",
            params: {
                store:"autoWhitelistEmailStore",
                sender:this.get("sender_name"),
                domain:this.get("sender_domain"),
                source:this.get("src")
            }
        });
    }
});