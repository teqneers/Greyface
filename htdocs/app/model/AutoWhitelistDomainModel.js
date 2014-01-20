Ext.define("Greyface.model.AutoWhitelistDomainModel",{
    extend:"Ext.data.Model",
    fields:[
        "sender_domain",
        "src",
        {
            name: "first_seen",
            type: "date",
            dateReadFormat: "Y-m-d H:i:s",
            dateWriteFormat: "timestamp"
        },
        {
            name: "last_seen",
            type: "date",
            dateReadFormat: "Y-m-d H:i:s",
            dateWriteFormat: "timestamp"
        }
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
                store:"autoWhitelistDomainStore",
                domain:this.get("sender_domain"),
                source:this.get("src")
            }
        });
    }
});