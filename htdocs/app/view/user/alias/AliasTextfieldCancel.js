Ext.define("Greyface.view.user.alias.AliasTextfieldCancel",{
    extend: 'Ext.form.field.Trigger',
    alias: 'widget.AliasfieldCancel',

    allowBlank: false,
    vtype:'email',
    triggerCls: 'x-form-clear-trigger',

    // override onTriggerClick
    onTriggerClick: function() {
        this.destroy();
    }
});
