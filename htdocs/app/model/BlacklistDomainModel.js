Ext.define("Greyface.model.BlacklistDomainModel",{
    extend:"Ext.data.Model",
    fields:[
        "domain"
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
                store:"blacklistDomainStore",
                domain:this.get("domain")
            }
        });
    }
});