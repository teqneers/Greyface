Ext.define("Greyface.model.BlacklistEmailModel",{
    extend:"Ext.data.Model",
    fields:[
        "email"
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
                store:"blacklistEmailStore",
                email:this.get("email")
            }
        });
    }
});