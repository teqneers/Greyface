Ext.define("Greyface.model.UserModel",{
    extend:"Ext.data.Model",
    fields:[
        "is_admin",
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
                store:"userAdminStore",
                username:this.get("username")
            }
        });
    },

    setPassword: function(newPassword) {
        Ext.Ajax.request({
            url: "api/CRUDRouter.php?action=setPassword",
            success: function(response, opts) {
                var decResponse = Ext.decode(response.responseText);
                console.log(decResponse.data[0]);
            },
            failure: function(response, opts) {
            },
            method: "GET",
            params: {
                store:"userAdminStore",
                username:this.get("username"),
                password:newPassword
            }
        });
    }
});