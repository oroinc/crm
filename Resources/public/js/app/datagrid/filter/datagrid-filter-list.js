/**
 * View that represents all grid filters
 *
 * @class   OroApp.DatagridFilterList
 * @extends OroApp.Filter.List
 */
OroApp.DatagridFilterList = OroApp.Filter.List.extend({
    /**
     * Initialize filter list options
     *
     * @param {Object} options
     * @param {OroApp.PageableCollection} [options.collection]
     * @param {Object} [options.filters]
     * @param {String} [options.addButtonHint]
     */
    initialize: function(options)
    {
        this.collection = options.collection;

        this.collection.on('beforeFetch', this._beforeCollectionFetch, this);
        this.collection.on('updateState', this._onUpdateCollectionState, this);

        OroApp.Filter.List.prototype.initialize.apply(this, arguments);
    },

    /**
     * Triggers when filter is updated
     *
     * @param {OroApp.Filter.AbstractFilter} filter
     * @protected
     */
    _onFilterUpdated: function(filter) {
        if (this.ignoreFiltersUpdateEvents) {
            return;
        }
        this.collection.state.currentPage = 1;
        this.collection.fetch();

        OroApp.Filter.List.prototype._onFilterUpdated.apply(this, arguments);
    },

    /**
     * Triggers before collection fetch it's data
     *
     * @protected
     */
    _beforeCollectionFetch: function(collection) {
        collection.state.filters = this._createState();
    },

    /**
     * Triggers when collection state is updated
     *
     * @param {OroApp.PageableCollection} collection
     */
    _onUpdateCollectionState: function(collection) {
        this.ignoreFiltersUpdateEvents = true;
        this._applyState(collection.state.filters || {});
        this.ignoreFiltersUpdateEvents = false;
    },

    /**
     * Create state according to filters parameters
     *
     * @return {Object}
     * @protected
     */
    _createState: function() {
        var state = {};
        _.each(this.filters, function(filter, name) {
            var shortName = '__' + name;
            if (filter.enabled) {
                if (!filter.isEmpty()) {
                    state[name] = filter.getValue();
                } else if (!filter.defaultEnabled) {
                    state[shortName] = 1;
                }
            } else if (filter.defaultEnabled) {
                state[shortName] = 0;
            }
        }, this);

        return state;
    },

    /**
     * Apply filter values from state
     *
     * @param {Object} state
     * @protected
     * @return {*}
     */
    _applyState: function(state) {
        _.each(this.filters, function(filter, name) {
            var shortName = '__' + name;
            if (_.has(state, name)) {
                var filterState = state[name];
                if (!_.isObject(filterState)) {
                    filterState = {
                        value: filterState
                    }
                }
                this.enableFilter(filter.setValue(filterState));
            } else if (_.has(state, shortName)) {
                if (Number(state[shortName])) {
                    this.enableFilter(filter.reset());
                } else {
                    this.disableFilter(filter.reset());
                }
            }
        }, this);

        return this;
    }
});
