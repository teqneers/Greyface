Ext.define("Greyface.model.WhitelistEmailModel",{
    extend:"Ext.data.Model",
    fields:[
        {
            name:"email",
            type:"string",
            serialize: function(value, record) {
                return record.oldEmail + "--->" + value;
            }
        }
    ],
    // Overwrite set method to save the original "email" value in "oldEmail" variable!
    // The serialize function of field "email" will concatenate the "oldEmail->" with the new "email" value which is setted here!
    // This is necessary because "email" is the value AND the primary key on server/db side. ExtJs cannot handle this in a proper way,
    // normally the id of a field does not change. So the update operation would only post the new value, so we have no id and the server
    // does not know which email-entry he should change (no WHERE clause in the UPDATE query)
    set: function(value) {
        if(value.email !== undefined) {
            this.oldEmail = this.get("email");
        }
        this.callParent(arguments);
    },
    oldEmail:"",
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
                store:"whitelistEmailStore",
                email:this.get("email")
            }
        });
    }
});