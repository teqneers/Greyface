Ext.define("Greyface.model.AliasModel",{
    extend:"Ext.data.Model",
    fields:[
        "alias_id",
        "username",
        "email"
    ],
    idProperty:'alias_id',

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
                store:"userAliasStore",
                alias_id:this.get("alias_id")
            }
        });
    }
});