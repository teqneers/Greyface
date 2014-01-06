Ext.define("Greyface.model.AliasModel",{
    extend:"Ext.data.Model",
    fields:[
        "alias_id",
        "user_id",
        "username",
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
                store:"userAliasStore",
                alias_id:this.get("alias_id")
            }
        });
    }
});