// Oro Application collection prototype for datagrid
OroApp.PageableCollection = Backbone.PageableCollection.extend({
    model: OroApp.Model,
    initialize: function(models, options) {
        _.extend(this.state, options.state);
        if (options.url) {
            this.url = options.url;
        }
        if (options.model) {
            this.model = options.model;
        }


        // users[_sortBy][username] = ASC
        _.extend(this.queryParams, {
            currentPage: 'users[_pager][_page]',
            pageSize: 'users[_pager][_per_page]',
            sortBy: 'users[_sort_by][%field%]',
            directions: {
                "-1": "ASC",
                "1": "DESC"
            }
        })

        OroApp.Collection.prototype.initialize.apply(this, arguments);
    },
    fetch: function (options) {
        var _extend = _.extend;
        var _omit = _.omit;
        var _clone = _.clone;
        var _each = _.each;
        var _pick = _.pick;
        var _contains = _.contains;
        var _isEmpty = _.isEmpty;
        var _pairs = _.pairs;
        var _invert = _.invert;
        var _isArray = _.isArray;
        var _isFunction = _.isFunction;
        var _keys = _.keys;
        var _isUndefined = _.isUndefined;
        var BBColProto = Backbone.Collection.prototype;
        var PageableProto = OroApp.PageableCollection.prototype;

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
            _extend(data, queryStringToParams(url.slice(qsi + 1)));
            url = url.slice(0, qsi);
        }

        options.url = url;
        options.data = data;

        data = this.processQueryParams(data, state);

        var fullCollection = this.fullCollection, links = this.links;

        if (mode != "server") {

            var self = this;
            var success = options.success;
            options.success = function (col, resp, opts) {

                // make sure the caller's intent is obeyed
                opts = opts || {};
                if (_isUndefined(options.silent)) delete opts.silent;
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
                        _extend({silent: true, sort: false}, opts));
                    if (fullCollection.comparator) fullCollection.sort();
                    fullCollection.trigger("reset", fullCollection, opts);
                }
                else { // fetching new page
                    fullCollection.add(models, _extend({at: fullCollection.length,
                        silent: true}, opts));
                    fullCollection.trigger("reset", fullCollection, opts);
                }

                if (success) success(col, resp, opts);
            };

            // silent the first reset from backbone
            return BBColProto.fetch.call(self, _extend({}, options, {silent: true}));
        }

        return BBColProto.fetch.call(this, options);
    },
    processQueryParams: function(data, state) {
        var _extend = _.extend;
        var _omit = _.omit;
        var _clone = _.clone;
        var _each = _.each;
        var _pick = _.pick;
        var _contains = _.contains;
        var _isEmpty = _.isEmpty;
        var _pairs = _.pairs;
        var _invert = _.invert;
        var _isArray = _.isArray;
        var _isFunction = _.isFunction;
        var _keys = _.keys;
        var _isUndefined = _.isUndefined;
        var BBColProto = Backbone.Collection.prototype;
        var PageableProto = OroApp.PageableCollection.prototype;

        // map params except directions
        var queryParams = this.mode == "client" ?
            _pick(this.queryParams, "sortKey", "order") :
            _omit(_pick(this.queryParams, _keys(PageableProto.queryParams)),
                "directions");

        var i, kvp, k, v, kvps = _pairs(queryParams), thisCopy = _clone(this);
        for (i = 0; i < kvps.length; i++) {
            kvp = kvps[i], k = kvp[0], v = kvp[1];
            v = _isFunction(v) ? v.call(thisCopy) : v;
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
        var extraKvps = _pairs(_omit(this.queryParams,
            _keys(PageableProto.queryParams)));
        for (i = 0; i < extraKvps.length; i++) {
            kvp = extraKvps[i];
            v = kvp[1];
            v = _isFunction(v) ? v.call(thisCopy) : v;
            data[kvp[0]] = v;
        }

        if (state.sortKey) {
            var key = this.queryParams.sortBy.replace('%field%', state.sortKey);
            data[key] = this.queryParams.directions[state.order];
        }

        delete data[queryParams.order];
        delete data[queryParams.sortKey];

        return data;
    }
});
