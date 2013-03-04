// Oro Application collection prototype for datagrid
OroApp.PageableCollection = Backbone.PageableCollection.extend({

    // basic model to store row data
    model: OroApp.Model,

    // initialize basic parameters from source options
    initialize: function(models, options) {
        _.extend(this.state, options.state);
        if (options.url) {
            this.url = options.url;
        }
        if (options.model) {
            this.model = options.model;
        }
        if (options.inputName) {
            this.inputName = options.inputName;
        }

        _.extend(this.queryParams, {
            currentPage: this.inputName + '[_pager][_page]',
            pageSize:    this.inputName + '[_pager][_per_page]',
            sortBy:      this.inputName + '[_sort_by][%field%]',
            directions: {
                "-1": "ASC",
                "1": "DESC"
            }
        });

        OroApp.Collection.prototype.initialize.apply(this, arguments);
    },

    // {'filter_key' => 'filter_value', ...}
    processFiltersParams: function(data, state) {
        if (state.filters) {
            _.each(state.filters, function(filterParameters, filterKey) {
                for (parameter in filterParameters) {
                    var queryParameter = this.inputName + '[_filter][' + filterKey + ']' + parameter;
                    data[queryParameter] = filterParameters[parameter];
                }
            }, this);
        }
        return data;
    },

    // { data : array, options : server_parameters }
    parse: function(resp, options) {
        this.state.totalRecords = resp.options.totalRecords;
        this.state = this._checkState(this.state);
        return resp.data;
    },

    // fetch collection data
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

    // process parameters which are sending to server
    processQueryParams: function(data, state) {
        var PageableProto = OroApp.PageableCollection.prototype;

        // map params except directions
        var queryParams = this.mode == "client" ?
            _.pick(this.queryParams, "sortKey", "order") :
            _.omit(_.pick(this.queryParams, _.keys(PageableProto.queryParams)),
                "directions");

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
            _.keys(PageableProto.queryParams)));
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
