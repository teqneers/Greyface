Ext.define("Greyface.model.AutoWhitelistEmailModel",{
    extend:"Ext.data.Model",
    fields:[
        "sender_name",
        "sender_domain",
        "src",
        "first_seen",
        "last_seen"
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
                store:"autoWhitelistEmailStore",
                sender:this.get("sender_name"),
                domain:this.get("sender_domain"),
                source:this.get("src")
            }
        });
    }
});