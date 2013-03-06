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

    /**
     * Initialize datagrid
     *
     * @param {Object} options
     */
    initialize: function(options) {
        this.collection = options.collection;

        this.collection.on('request', this._onRequest, this);
        this.collection.on('sync', this._onSync, this);

        if (options.noDataHint) {
            this.noDataHint = options.noDataHint.replace('\n', '<br />');
        }

        Backgrid.Grid.prototype.initialize.apply(this, arguments);
    },

    /**
     * Renders the grid, no data block and loading mask
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        this.$el = this.$el.append($(this.template()));

        this._renderGrid(this.$(this.selectors.grid));
        this._renderNoDataBlock(this.$(this.selectors.noDataBlock));
        this._renderLoadingMask(this.$(this.selectors.loadingMask));

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
     * @private
     */
    _renderGrid: function($el) {
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
    _renderLoadingMask: function($el) {
        this.loadingMask = new OroApp.LoadingMask({
            el: $el
        }).render();
    },

    /**
     * Render no data block.
     *
     * @param {Object} $el
     * @private
     */
    _renderNoDataBlock: function($el) {
        $el.append($(this.noDataTemplate({
            hint: this.noDataHint
        })));
        this.$(this.selectors.noDataBlock).hide();
    },

    /**
     * Triggers when on collection "request" event
     *
     * @private
     */
    _onRequest: function() {
        this.loadingMask.show();
    },

    /**
     * Triggers when on collection "sync" event
     *
     * @private
     */
    _onSync: function() {
        this.loadingMask.hide();
        if (this.collection.models.length > 0) {
            this.$(this.selectors.grid).show();
            this.$(this.selectors.noDataBlock).hide();
        } else {
            this.$(this.selectors.grid).hide();
            this.$(this.selectors.noDataBlock).show();
        }
    }
});
