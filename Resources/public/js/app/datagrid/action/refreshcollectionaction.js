var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};
OroApp.Datagrid.Action = OroApp.Datagrid.Action || {};

/**
 * Refreshes collection
 *
 * @class   OroApp.Datagrid.Action.RefreshCollectionAction
 * @extends OroApp.Datagrid.Action.AbstractAction
 */
OroApp.Datagrid.Action.RefreshCollectionAction = OroApp.Datagrid.Action.AbstractAction.extend({

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
        this.collection.fetch();
    }
});
