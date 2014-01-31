Ext.define("Greyface.model.UserModel",{
    extend:"Ext.data.Model",
    fields:[
        {name: "username", type:"string"},
        {name: "email", type:"string"},
        {name: "user_id"},
        {
            name: "is_admin",
            type:'int',
            convert: function(value, record){
                if (Number(value) == 1) {
                    return true;
                } else {
                    return false;
                }
            },
            serialize: function(value, record) {
                if (value) {
                    return 1;
                } else {
                    return 0;
                }
            }
        }
    ],
    idProperty:'user_id',

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
            method: "POST",
            params: {
                store:"userAdminStore",
                username:this.get("username"),
                password:newPassword
            }
        });
    }
});