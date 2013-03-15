/**
 * Delete action with confirm dialog, triggers REST DELETE request
 *
 * @class   OroApp.DatagridActionDelete
 * @extends OroApp.DatagridAction
 */
OroApp.DatagridActionDelete = OroApp.DatagridAction.extend({

    /** @property Backbone.BootstrapModal */
    confirmModal: undefined,

    /**
     * Execute model delete using REST service
     */
    execute: function() {
        this.getConfirmDialog().open(this.confirmDelete);
    },

    /**
     * Confirm delete item
     */
    confirmDelete: function() {
        console.log('Confirm delete');
    },

    /**
     * Get view for confirm dialog
     *
     * @return {Backbone.BootstrapModal}
     */
    getConfirmDialog: function() {
        if (!this.confirmModal) {
            this.confirmModal = new Backbone.BootstrapModal({
                content: 'Are you sure you want to delete this item?'
            });
        }
        return this.confirmModal;
    }
});
