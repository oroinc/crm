/**
 * Delete action with confirm dialog, triggers REST DELETE request
 *
 * @class   OroApp.DatagridActionDelete
 * @extends OroApp.DatagridAction
 */
OroApp.DatagridActionDelete = OroApp.DatagridAction.extend({

    /** @property Backbone.BootstrapModal */
    errorModal: undefined,

    /** @property Backbone.BootstrapModal */
    confirmModal: undefined,

    /**
     * Execute delete model
     */
    execute: function() {
        this.getConfirmDialog().open($.proxy(this.doDelete, this));
    },

    /**
     * Confirm delete item
     */
    doDelete: function() {
        var self = this;
        this.model.destroy({
            url: this.getLink(),
            wait: true,
            error: function() {
                self.getErrorDialog().open();
            }
        });
    },

    /**
     * Get view for confirm modal
     *
     * @return {Backbone.BootstrapModal}
     */
    getConfirmDialog: function() {
        if (!this.confirmModal) {
            this.confirmModal = new Backbone.BootstrapModal({
                title: 'Delete Confirmation',
                content: 'Are you sure you want to delete this item?'
            });
        }
        return this.confirmModal;
    },

    /**
     * Get view for error modal
     *
     * @return {Backbone.BootstrapModal}
     */
    getErrorDialog: function() {
        if (!this.errorModal) {
            this.confirmModal = new Backbone.BootstrapModal({
                title: 'Delete Error',
                content: 'Cannot delete item.',
                cancelText: false
            });
        }
        return this.confirmModal;
    }
});
