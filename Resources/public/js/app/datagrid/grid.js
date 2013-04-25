var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};

/**
 * Basic datagrid class
 *
 * @class   OroApp.Datagrid.Grid
 * @extends Backgrid.Grid
 */
OroApp.Datagrid.Grid = Backgrid.Grid.extend({
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
    header: OroApp.Datagrid.Header,

    /** @property */
    body: OroApp.Datagrid.Body,

    /** @property */
    selectors: {
        grid:        '.grid',
        toolbar:     '.toolbar',
        noDataBlock: '.no-data',
        loadingMask: '.loading-mask'
    },

    /** @property {Object} */
    toolbarOptions: {},

    /** @property {OroApp.Datagrid.Toolbar} */
    toolbar: OroApp.Datagrid.Toolbar,

    /** @property {OroApp.LoadingMask} */
    loadingMask: OroApp.LoadingMask,

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
        cell: OroApp.Datagrid.Action.Cell,
        headerCell: Backgrid.HeaderCell.extend({
            className: 'action-column'
        }),
        sortable: false
    },

    /** @property {Function} */
    rowClickAction: undefined,

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

        _.defaults(options, {
            rowClickAction: this.rowClickAction
        });

        if (options.loadingMask) {
            this.loadingMask = options.loadingMask;
        }
        this.loadingMask = new this.loadingMask();

        _.extend(this.toolbarOptions, {collection: this.collection}, options.toolbarOptions);
        this.toolbar = new this.toolbar(_.extend(this.toolbarOptions));

        Backgrid.Grid.prototype.initialize.apply(this, arguments);
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
    }
});
