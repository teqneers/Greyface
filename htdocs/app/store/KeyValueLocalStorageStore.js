Ext.define("Greyface.store.KeyValueLocalStorageStore",{
    extend: "Ext.data.Store",
    model: "Greyface.model.KeyValueLocalStorageModel",
    autoLoad:true,
    autoSync: true,

    store: function(key, value) {
        if ( this.find('key', key) > -1 ) {
            var record =  this.getAt(this.find('key', key));
            record.set('value', value);
            record.save()
        } else {
            this.add({key: key, value: value})
        }
    },
    get: function(key) {
        if ( this.find('key', key) > -1 ) {
            return this.getAt(this.find('key', key)).get('value')
        } else {
            return false;
        }
    },
    has: function(key) {
        if ( this.find('key', key) > -1 ) {
            return true;
        } else {
            return false;
        }
    }
});