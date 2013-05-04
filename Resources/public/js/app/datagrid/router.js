var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};

/**
 * Router for basic datagrid
 *
 * @class   Oro.Datagrid.Router
 * @extends Backbone.Router
 */
Oro.Datagrid.Router = Backbone.Router.extend({
    /** @property */
    routes: {
        "g/*encodedStateData": "changeState",
        "": "init"
    },

    /**
     * Binded collection, passed in constructor as option
     *
     * @property {Oro.PageableCollection}
     */
    collection: null,

    /**
     * Initial state of binded collection, passed in constructor
     *
     * @property {Object}
     */
    _initState: null,

    /**
     * Initialize router
     *
     * @param {Object} options
     * @param {Oro.PageableCollection} options.collection Collection of models.
     */
    initialize: function(options) {
        options = options || {};
        if (!options.collection) {
            throw new TypeError("'collection' is required");
        }

        this.collection = options.collection;
        this._initState = _.clone(this.collection.state);

        this.collection.on('beforeReset', this._handleStateChange, this);

        Backbone.Router.prototype.initialize.apply(this, arguments);
    },

    /**
     * Triggers when collection is has new state and fetched
     *
     * @param {Oro.PageableCollection} collection
     * @param {Object} options Fetch options
     * @private
     */
    _handleStateChange: function(collection, options) {
        options = options || {};
        if (options.ignoreSaveStateInUrl) {
            return;
        }
        var encodedStateData = collection.encodeStateData(collection.state);
        this.navigate('g/' + encodedStateData);
    },

    /**
     * Route for change state of collection.
     *
     * @param {String} encodedStateData String represents encoded state stored in URL
     */
    changeState: function(encodedStateData) {
        var state = this.collection.decodeStateData(encodedStateData);
        this.collection.updateState(state);
        this.collection.fetch({
            ignoreSaveStateInUrl: true
        });
    },

    /**
     * Route for initializing collection. Collection will retrieve initial state and call fetch.
     */
    init: function() {
        this.collection.updateState(this._initState);
        this.collection.fetch({
            ignoreSaveStateInUrl: true
        });
    }
});
