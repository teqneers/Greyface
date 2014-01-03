Ext.define("Greyface.model.AutoWhitelistDomainModel",{
    extend:"Ext.data.Model",
    fields:[
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
                store:"autoWhitelistDomainStore",
                domain:this.get("sender_domain"),
                source:this.get("src")
            }
        });
    }
});