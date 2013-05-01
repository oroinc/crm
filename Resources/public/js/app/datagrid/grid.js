var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};

/**
 * Basic grid class.
 *
 * Triggers events:
 *  - "cellEdited" when one of cell of grid body row is edited
 *  - "rowClicked" when row of grid body is clicked
 *
 * @class   Oro.Datagrid.Grid
 * @extends Backgrid.Grid
 */
Oro.Datagrid.Grid = Backgrid.Grid.extend({
    /** @property */
    tagName: 'div',

    /** @property */
    requestsCount: 0,

    /** @property */
    className: 'clearfix',

    /** @property */
    template: _.template(
        '<div class="toolbar"></div>' +
        '<div class="grid-container" style="position: relative;">' +
            '<table class="grid table-hover table table-bordered table-condensed"></table>' +
            '<div class="no-data"></div>' +
            '<div class="loading-mask"></div>' +
        '</div>'
    ),

    /** @property */
    header: Oro.Datagrid.Header,

    /** @property */
    body: Oro.Datagrid.Body,

    /** @property */
    selectors: {
        grid:        '.grid',
        toolbar:     '.toolbar',
        noDataBlock: '.no-data',
        loadingMask: '.loading-mask'
    },

    /** @property {Object} */
    toolbarOptions: {},

    /** @property {Oro.Datagrid.Toolbar} */
    toolbar: Oro.Datagrid.Toolbar,

    /** @property {Oro.LoadingMask} */
    loadingMask: Oro.LoadingMask,

    /** @property */
    noDataTemplate: _.template('<span><%= hint %><span>'),

    /** @property */
    noDataHint: 'No data found.',

    /** @property */
    actionsColumn: Backgrid.Column,

    /** @property */
    actionsColumnAttributes: {
        name: '',
        label: '',
        editable: false,
        cell: Oro.Datagrid.Action.Cell,
        headerCell: Backgrid.HeaderCell.extend({
            className: 'action-column'
        }),
        sortable: false
    },

    /** @property {Function} */
    rowClickAction: undefined,

    /** @property {String} */
    rowClickActionClass: 'row-click-action',

    /**
     * Initialize datagrid
     *
     * @param {Object} options
     * @param {Backbone.Collection} options.collection
     * @param {Backbone.Collection|Array} options.columns
     * @param {Object} [options.toolbarOptions]
     * @param {String} [options.noDataHint]
     * @param {Array} [options.actions]
     */
    initialize: function(options) {
        options = options || {};

        if (!options.collection) {
            throw new TypeError("'collection' is required")
        }

        if (!options.columns) {
            throw new TypeError("'columns' is required")
        }

        this.collection = options.collection;

        this.collection.on('request', function(model, xhr, options) {
            this.beforeRequest();
            var self = this;
            var always = xhr.always;
            xhr.always = function() {
                always.apply(this, arguments);
                self.afterRequest();
            }
        }, this);

        this.collection.on('remove', this.onRemove, this);

        if (options.noDataHint) {
            this.noDataHint = options.noDataHint.replace('\n', '<br />');
        }

        if (!_.isEmpty(options.actions)) {
            options.columns.push(this.createActionsColumn(options.actions));
            this.rowClickAction = this.filterOnClickAction(options.actions);
        }

        if (this.rowClickAction) {
            options.rowClassName = this.rowClickActionClass + ' ' + (options.rowClassName || '');
        }

        if (options.loadingMask) {
            this.loadingMask = options.loadingMask;
        }
        this.loadingMask = new this.loadingMask();

        _.extend(this.toolbarOptions, {collection: this.collection}, options.toolbarOptions);
        this.toolbar = new this.toolbar(_.extend(this.toolbarOptions));

        Backgrid.Grid.prototype.initialize.apply(this, arguments);

        this._listenToBodyEvents();
    },

    /**
     * Listen to events of body, proxies events "rowClicked" and "rowEdited", handle run of rowClickAction if required
     *
     * @private
     */
    _listenToBodyEvents: function() {
        this.listenTo(this.body, 'rowClicked', function(row) {
            this.trigger('rowClicked', this, row);
            if (this.rowClickAction) {
                var action = this._createRowClickAction(this.rowClickAction, row);
                action.run();
            }
        });
        this.listenTo(this.body, 'cellEdited', function(row, cell) {
            this.trigger('cellEdited', this, row, cell);
        });
    },

    /**
     * Create row click action
     *
     * @param {*} action Action prototype
     * @param {Oro.Datagrid.Row} row
     * @return {Oro.Datagrid.Action.AbstractAction}
     * @private
     */
    _createRowClickAction: function(action, row) {
        return new action({
            model: row.model
        });
    },

    /**
     * Creates actions column
     *
     * @param {Array} actions
     * @return {Backgrid.Column}
     * @protected
     */
    createActionsColumn: function(actions) {
        var filteredActions = _.filter(actions, function(action) {
            return !action.prototype.runOnRowClick;
        });
        return new this.actionsColumn(_.extend(
            this.actionsColumnAttributes, {
                actions: filteredActions
            }
        ));
    },

    /**
     * Filters action with runOnRowClick flag
     *
     * @param actions
     * @return {*}
     */
    filterOnClickAction: function(actions) {
        var filtered = _.filter(actions, function(action) {
            return action.prototype.runOnRowClick;
        });
        if (filtered.length) {
            return filtered[0];
        }
    },

    /**
     * Renders the grid, no data block and loading mask
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        this.$el = this.$el.append($(this.template()));

        this.renderToolbar();
        this.renderGrid();
        this.renderNoDataBlock();
        this.renderLoadingMask();

        /**
         * Backbone event. Fired when the grid has been successfully rendered.
         * @event rendered
         */
        this.trigger("rendered");

        return this;
    },

    /**
     * Renders the grid's header, then footer, then finally the body.
     *
     * @protected
     */
    renderGrid: function() {
        var $el = this.$(this.selectors.grid);

        $el.append(this.header.render().$el);
        if (this.footer) {
            $el.append(this.footer.render().$el);
        }
        $el.append(this.body.render().$el);
    },

    /**
     * Renders grid toolbar.
     *
     * @protected
     */
    renderToolbar: function() {
        this.$(this.selectors.toolbar).append(this.toolbar.render().$el);
    },

    /**
     * Renders loading mask.
     *
     * @protected
     */
    renderLoadingMask: function() {
        this.$(this.selectors.loadingMask).append(this.loadingMask.render().$el);
        this.loadingMask.hide();
    },

    /**
     * Render no data block.
     *
     * @protected
     */
    renderNoDataBlock: function() {
        this.$(this.selectors.noDataBlock).append($(this.noDataTemplate({
            hint: this.noDataHint
        }))).hide();
    },

    /**
     * Triggers when collection "request" event fired
     *
     * @protected
     */
    beforeRequest: function() {
        this.requestsCount++;
        this.loadingMask.show();
        this.toolbar.disable();
    },

    /**
     * Triggers when collection request is done
     *
     * @protected
     */
    afterRequest: function() {
        this.requestsCount--;
        if (this.requestsCount == 0) {
            this.loadingMask.hide();
            this.toolbar.enable();
            if (this.collection.models.length > 0) {
                this.$(this.selectors.grid).show();
                this.$(this.selectors.noDataBlock).hide();
            } else {
                this.$(this.selectors.grid).hide();
                this.$(this.selectors.noDataBlock).show();
            }
        }
    },

    /**
     * Triggers when collection "remove" event fired
     *
     * @protected
     */
    onRemove: function() {
        this.collection.fetch();
    },

    /**
     * Set additional parameter to send on server
     *
     * @param {String} name
     * @param value
     */
    setAdditionalParameter: function(name, value) {
        var state = this.collection.state;
        if (!_.has(state, 'parameters')) {
            state.parameters = {};
        }

        state.parameters[name] = value;
    }
});
