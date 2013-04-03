/**
 * Pageable collection
 *
 * @class   OroApp.PageableCollection
 * @extends Backbone.PageableCollection
 */
OroApp.PageableCollection = Backbone.PageableCollection.extend({
    /**
     * Basic model to store row data
     *
     * @property {Function}
     */
    model: OroApp.Model,

    /**
     * Object declares state keys that will be involved in URL-state saving with their shorthands
     *
     * @property {Object}
     */
    stateShortKeys: {
        currentPage: 'i',
        pageSize: 'p',
        sortKey: 's',
        order: 'o',
        filters: 'f'
    },

    /**
     * Initialize basic parameters from source options
     *
     * @param models
     * @param options
     */
    initialize: function(models, options) {
        options = options || {};
        if (options.state) {
            _.extend(this.state, options.state);
        }
        if (options.url) {
            this.url = options.url;
        }
        if (options.model) {
            this.model = options.model;
        }
        if (options.inputName) {
            this.inputName = options.inputName;
        }

        this.on('remove', this.onRemove, this);

        _.extend(this.queryParams, {
            currentPage: this.inputName + '[_pager][_page]',
            pageSize:    this.inputName + '[_pager][_per_page]',
            sortBy:      this.inputName + '[_sort_by][%field%]',
            directions: {
                "-1": "ASC",
                "1": "DESC"
            },
            totalRecords: undefined,
            totalPages: undefined
        });

        OroApp.Collection.prototype.initialize.apply(this, arguments);
    },

    /**
     * Triggers when model is removed from collection.
     *
     * Ensure that state is changed after concrete model removed from collection.
     *
     * @protected
     */
    onRemove: function() {
        if (this.state.totalRecords > 0) {
            this.state.totalRecords--;
        }
    },

    /**
     * Encode state object to string
     *
     * @param {Object} stateObject
     * @return {String}
     */
    encodeStateData: function(stateObject) {
        var data = _.pick(stateObject, _.keys(this.stateShortKeys));
        data = OroApp.invertKeys(data, this.stateShortKeys);
        return OroApp.packToQueryString(data);
    },

    /**
     * Decode state object from string, operation is invert for encodeStateData.
     *
     * @param {String} stateString
     * @return {Object}
     */
    decodeStateData: function(stateString) {
        var data = OroApp.unpackFromQueryString(stateString);
        data = OroApp.invertKeys(data, _.invert(this.stateShortKeys));
        return data;
    },

    /**
     * @param {Object} data
     * @param {Object} state
     * @return {Object}
     */
    processFiltersParams: function(data, state) {
        if (state.filters) {
            _.extend(
                data,
                this._generateParameterStrings(state.filters, this.inputName + '[_filter]')
            );
        }
        return data;
    },

    /**
     *
     * @param {Object} parameters
     * @param {String} prefix
     * @return {Object}
     * @private
     */
    _generateParameterStrings: function(parameters, prefix) {
        var localStrings = {};
        var localPrefix = prefix;
        _.each(parameters, function(filterParameters, filterKey) {
            if (filterKey.substr(0, 2) != '__') {
                var filterKeyString = localPrefix + '[' + filterKey + ']';
                if (_.isObject(filterParameters) && !_.isArray(filterParameters)) {
                    _.extend(
                        localStrings,
                        this._generateParameterStrings(filterParameters, filterKeyString)
                    );
                } else {
                    localStrings[filterKeyString] = filterParameters;
                }
            }
        }, this);

        return localStrings;
    },

    // { data : array, options : server_parameters }
    parse: function(resp, options) {
        this.state.totalRecords = resp.options.totalRecords;
        this.state = this._checkState(this.state);
        return resp.data;
    },

    reset: function(models, options) {
        this.trigger('beforeReset', this, options);
        OroApp.Collection.prototype.reset.apply(this, arguments);
    },

    /**
     * Extends and checks state
     *
     * @param {Object} state
     */
    extendState: function(state) {
        this.state = this._checkState(_.extend({}, this.state, state))
    },

    /**
     * @inheritDoc
     */
    _checkState: function (state) {
        var mode = this.mode;
        var links = this.links;
        var totalRecords = state.totalRecords;
        var pageSize = state.pageSize;
        var currentPage = state.currentPage;
        var firstPage = state.firstPage;
        var totalPages = state.totalPages;

        if (totalRecords != null && pageSize != null && currentPage != null &&
            firstPage != null && (mode == "infinite" ? links : true)) {

            state.totalRecords = totalRecords = this.finiteInt(totalRecords, "totalRecords");
            state.pageSize = pageSize = this.finiteInt(pageSize, "pageSize");
            state.currentPage = currentPage = this.finiteInt(currentPage, "currentPage");
            state.firstPage = firstPage = this.finiteInt(firstPage, "firstPage");

            if (pageSize < 1) {
                throw new RangeError("`pageSize` must be >= 1");
            }

            state.totalPages = totalPages = state.totalPages = Math.ceil(totalRecords / pageSize);

            if (firstPage < 0 || firstPage > 1) {
                throw new RangeError("`firstPage` must be 0 or 1");
            }

            state.lastPage = firstPage === 0 ? totalPages - 1 : totalPages;

            // page out of range
            if (currentPage > state.lastPage) {
                state.currentPage = currentPage = state.lastPage;
            }

            // no results returned
            if (totalRecords == 0) {
                state.currentPage = currentPage = firstPage;
            }

            if (mode == "infinite") {
                if (!links[currentPage + '']) {
                    throw new RangeError("No link found for page " + currentPage);
                }
            } else if (totalPages > 0) {
                if (firstPage === 0 && (currentPage < firstPage || currentPage >= totalPages)) {
                    throw new RangeError("`currentPage` must be firstPage <= currentPage < totalPages if 0-based. Got " + currentPage + '.');
                }
                else if (firstPage === 1 && (currentPage < firstPage || currentPage > totalPages)) {
                    throw new RangeError("`currentPage` must be firstPage <= currentPage <= totalPages if 1-based. Got " + currentPage + '.');
                }
            } else if (currentPage !== firstPage ) {
                throw new RangeError("`currentPage` must be " + firstPage + ". Got " + currentPage + '.');
            }
        }

        return state;
    },

    /**
     * Asserts that val is finite integer.
     *
     * @param {*} val
     * @param {String} name
     * @return {Integer}
     * @protected
     */
    finiteInt: function(val, name) {
        val *= 1;
        if (!_.isNumber(val) || _.isNaN(val) || !_.isFinite(val) || ~~val !== val) {
            throw new TypeError("`" + name + "` must be a finite integer");
        }
        return val;
    },

    /**
     * Fetch collection data
     */
    fetch: function (options) {
        var BBColProto = Backbone.Collection.prototype;

        options = options || {};

        var state = this._checkState(this.state);

        var mode = this.mode;

        if (mode == "infinite" && !options.url) {
            options.url = this.links[state.currentPage];
        }

        var data = options.data || {};

        // dedup query params
        var url = options.url || _.result(this, "url") || '';
        var qsi = url.indexOf('?');
        if (qsi != -1) {
            _.extend(data, queryStringToParams(url.slice(qsi + 1)));
            url = url.slice(0, qsi);
        }

        options.url = url;
        options.data = data;

        data = this.processQueryParams(data, state);
        data = this.processFiltersParams(data, state);

        var fullCollection = this.fullCollection, links = this.links;

        if (mode != "server") {

            var self = this;
            var success = options.success;
            options.success = function (col, resp, opts) {

                // make sure the caller's intent is obeyed
                opts = opts || {};
                if (_.isUndefined(options.silent)) delete opts.silent;
                else opts.silent = options.silent;

                var models = col.models;
                var currentPage = state.currentPage;

                if (mode == "client") resetQuickly(fullCollection, models, opts);
                else if (links[currentPage]) { // refetching a page
                    var pageSize = state.pageSize;
                    var pageStart = (state.firstPage === 0 ?
                        currentPage :
                        currentPage - 1) * pageSize;
                    var fullModels = fullCollection.models;
                    var head = fullModels.slice(0, pageStart);
                    var tail = fullModels.slice(pageStart + pageSize);
                    fullModels = head.concat(models).concat(tail);
                    fullCollection.update(fullModels,
                        _.extend({silent: true, sort: false}, opts));
                    if (fullCollection.comparator) fullCollection.sort();
                    fullCollection.trigger("reset", fullCollection, opts);
                }
                else { // fetching new page
                    fullCollection.add(models, _.extend({at: fullCollection.length,
                        silent: true}, opts));
                    fullCollection.trigger("reset", fullCollection, opts);
                }

                if (success) success(col, resp, opts);
            };

            // silent the first reset from backbone
            return BBColProto.fetch.call(self, _.extend({}, options, {silent: true}));
        }

        return BBColProto.fetch.call(this, options);
    },

    /**
     * Process parameters which are sending to server
     *
     * @param {Object} data
     * @param {Object} state
     * @return {Object}
     */
    processQueryParams: function(data, state) {
        var pageablePrototype = OroApp.PageableCollection.prototype;

        // map params except directions
        var queryParams = this.mode == "client" ?
            _.pick(this.queryParams, "sortKey", "order") :
            _.omit(_.pick(this.queryParams, _.keys(pageablePrototype.queryParams)), "directions");

        var i, kvp, k, v, kvps = _.pairs(queryParams), thisCopy = _.clone(this);
        for (i = 0; i < kvps.length; i++) {
            kvp = kvps[i], k = kvp[0], v = kvp[1];
            v = _.isFunction(v) ? v.call(thisCopy) : v;
            if (state[k] != null && v != null) {
                data[v] = state[k];
            }
        }

        // fix up sorting parameters
        if (state.sortKey && state.order) {
            data[queryParams.order] = this.queryParams.directions[state.order + ""];
        }
        else if (!state.sortKey) delete data[queryParams.order];

        // map extra query parameters
        var extraKvps = _.pairs(_.omit(this.queryParams,
            _.keys(pageablePrototype.queryParams)));
        for (i = 0; i < extraKvps.length; i++) {
            kvp = extraKvps[i];
            v = kvp[1];
            v = _.isFunction(v) ? v.call(thisCopy) : v;
            data[kvp[0]] = v;
        }

        if (state.sortKey) {
            var key = this.queryParams.sortBy.replace('%field%', state.sortKey);
            data[key] = this.queryParams.directions[state.order];
        }

        // unused parameters
        delete data[queryParams.order];
        delete data[queryParams.sortKey];

        return data;
    }
});
