/**
 * Router for basic datagrid
 *
 * @class   OroApp.DatagridRouter
 * @extends OroApp.Router
 */
OroApp.DatagridRouter = OroApp.Router.extend({
    /** @property */
    routes: {
        "g/*encodedStateData": "changeState",
        "": "init"
    },

    /**
     * Binded collection, passed in constructor as option
     *
     * @property {OroApp.PageableCollection}
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
     * @param {OroApp.PageableCollection} [options.collection] Collection of models.
     */
    initialize: function(options) {
        this.collection = options.collection;
        this._initState = _.clone(this.collection.state);

        this.collection.on('reset', this._handleStateChange, this);

        OroApp.Router.prototype.initialize.apply(this, arguments);
    },

    /**
     * Triggers when collection is has new state and fetched
     *
     * @param {OroApp.PageableCollection} collection
     * @param {Object} options Fetch options
     * @private
     */
    _handleStateChange: function(collection, options) {
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
        _.extend(this.collection.state, state);
        this.collection.fetch({
            ignoreSaveStateInUrl: true
        });
    },

    /**
     * Route for initializing collection. Collection will retrieve initial state and call fetch.
     */
    init: function() {
        _.extend(this.collection.state, this._initState);
        this.collection.fetch({
            ignoreSaveStateInUrl: true
        });
    }
});
