var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};
OroApp.Datagrid.Action = OroApp.Datagrid.Action || {};

/**
 * Resets collection to initial state
 *
 * @class   OroApp.Datagrid.Action.ResetCollectionAction
 * @extends OroApp.Datagrid.Action.AbstractAction
 */
OroApp.Datagrid.Action.ResetCollectionAction = OroApp.Datagrid.Action.AbstractAction.extend({

    /** @property OroApp.PageableCollection */
    collection: undefined,

    /**
     * Initialize action
     *
     * @param {Object} options
     * @param {Backbone.Model} options.collection Collection
     * @throws {TypeError} If collection is undefined
     */
    initialize: function(options) {
        options = options || {};

        if (!options.collection) {
            throw new TypeError("'collection' is required");
        }
        this.collection = options.collection;

        OroApp.Datagrid.Action.AbstractAction.prototype.initialize.apply(this, arguments);
    },

    /**
     * Execute delete model
     */
    execute: function() {
        this.collection.updateState(this.collection.initialState);
        this.collection.fetch();
    }
});
