Ext.define("Greyface.model.GreylistModel",{
    extend:"Ext.data.Model",
    fields:[
        "sender_name",
        "sender_domain",
        "source",
        "alias_name",
        "first_seen",
        "username",
        "recipient"
    ],
    deleteItem: function() {
        Ext.Ajax.request({
            url: "api/CRUDRouter.php?action=delete",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse.data[0]);
            },
            failure: function(response, opts) {
            },
            method: "GET",
            params: {
                store:"greylistStore",
                senderName:this.get("sender_name"),
                domainName:this.get("sender_domain"),
                src:this.get("source"),
                rcpt:this.get("recipient")
            }
        });
    },
    toWhitelist: function() {
        Ext.Ajax.request({
            url: "api/CRUDRouter.php?action=toWhitelist",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse.data[0]);
            },
            failure: function(response, opts) {
            },
            method: "GET",
            params: {
                store:"greylistStore",
                senderName:this.get("sender_name"),
                domainName:this.get("sender_domain"),
                src:this.get("source"),
                rcpt:this.get("recipient")
            }
        });
    }
});