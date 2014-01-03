Ext.define("Greyface.tools.Registry",{
    extend: "Ext.data.Store",
    singleton:true,
    fields: ["key", "value"],
    data:[
        {key:"activeToolbar", value:"gf_greylistToolbar"},
        {key:"activeGridpanel", value:"gf_greylistPanel"}
    ],
    exists: function($key) {
        var result = this.get($key);
        if (result == null) {
            return false;
        } else {
            return true;
        }
    },
    set: function($key, $value) {
        this.add({key:$key, value:$value});
    },
    get: function($key) {
        var row = this.findExact("key", $key);
        if (row == -1) {
            return null;
        } else {
            return this.getAt(row).get("value");
        }
    }
});