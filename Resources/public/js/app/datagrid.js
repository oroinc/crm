/**
 * Basic datagrid class
 *
 * @class   OroApp.Datagrid
 * @extends Backgrid.Grid
 */
OroApp.Datagrid = Backgrid.Grid.extend({
    /** @property */
    tagName: 'div',

    /** @property */
    className: 'clearfix scroll-holder',

    /** @property */
    template: _.template(
        '<table class="grid table-hover table table-bordered table-condensed"></table>' +
        '<div class="no-data"></div>' +
        '<div class="loading-mask"></div>'
    ),

    /** @property */
    header: OroApp.DatagridHeader,

    /** @property */
    selectors: {
        grid:        '.grid',
        noDataBlock: '.no-data',
        loadingMask: '.loading-mask'
    },

    /** @property */
    noDataTemplate: _.template('<span><%= hint %><span>'),

    /** @property */
    noDataHint: 'No data found.',

    actionsColumn: Backgrid.Column,
    actionsColumnAttributes: {
        name: '',
        label: '',
        editable: false,
        cell: OroApp.DatagridActionCell
    },

    /**
     * Initialize datagrid
     *
     * @param {Object} options
     */
    initialize: function(options) {
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
        }

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
        return new this.actionsColumn(_.extend(
            this.actionsColumnAttributes, {
                actions: actions
            }
        ));
    },

    /**
     * Renders the grid, no data block and loading mask
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        this.$el = this.$el.append($(this.template()));

        this.renderGrid(this.$(this.selectors.grid));
        this.renderNoDataBlock(this.$(this.selectors.noDataBlock));
        this.renderLoadingMask(this.$(this.selectors.loadingMask));

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
     * @param {Object} $el
     * @protected
     */
    renderGrid: function($el) {
        $el.append(this.header.render().$el);
        if (this.footer) {
            $el.append(this.footer.render().$el);
        }
        $el.append(this.body.render().$el);
    },

    /**
     * Renders loading mask.
     *
     * @param {Object} $el
     * @private
     */
    renderLoadingMask: function($el) {
        this.loadingMask = new OroApp.LoadingMask({
            el: $el
        }).render();
    },

    /**
     * Render no data block.
     *
     * @param {Object} $el
     * @protected
     */
    renderNoDataBlock: function($el) {
        $el.append($(this.noDataTemplate({
            hint: this.noDataHint
        })));
        this.$(this.selectors.noDataBlock).hide();
    },

    /**
     * Triggers when collection "request" event fired
     *
     * @protected
     */
    beforeRequest: function() {
        this.loadingMask.show();
    },

    /**
     * Triggers when collection request is done
     *
     * @protected
     */
    afterRequest: function() {
        this.loadingMask.hide();
        if (this.collection.models.length > 0) {
            this.$(this.selectors.grid).show();
            this.$(this.selectors.noDataBlock).hide();
        } else {
            this.$(this.selectors.grid).hide();
            this.$(this.selectors.noDataBlock).show();
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
