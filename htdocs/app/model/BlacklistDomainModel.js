Ext.define("Greyface.model.BlacklistDomainModel",{
    extend:"Ext.data.Model",
    fields:[
        {
            name:"domain",
            type:"string",
            serialize: function(value, record) {
                console.log("serialize: " + record.oldDomain + "->" + value);
                return record.oldDomain + "->" + value;
            }
        }
    ],
    // Overwrite set method to save the original "domain" value in "oldDomain" variable!
    // The serialize function of field "domain" will concatenate the "oldDomain->" with the new "domain" value which is setted here!
    // This is necessary because "domain" is the value AND the primary key on server/db side. ExtJs cannot handle this in a proper way,
    // normally the id of a field does not change. So the update operation would only post the new value, so we have no id and the server
    // does not know which domain-entry he should change (no WHERE clause in the UPDATE query)
    set: function(value) {
        if(value.domain !== undefined) {
            this.oldDomain = this.get("domain");
        }
        this.callParent(arguments);
    },
    oldDomain:"",

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