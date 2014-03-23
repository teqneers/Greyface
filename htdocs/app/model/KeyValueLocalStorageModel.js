Ext.define("Greyface.model.KeyValueLocalStorageModel",{
    extend:"Ext.data.Model",
    fields:[
        'key',
        'value'
    ],
    proxy: {
        type: 'localstorage',
        id  : 'KeyValueLocalStorageModel'
    }
});