OroApp.Datagrid = Backgrid.Grid.extend({
    /** @property */
    tagName: 'div',

    /** @property */
    className: 'clearfix scroll-holder',

    template:_.template(
        '<table class="grid table-hover table table-bordered table-condensed"></table>' +
        '<div class="loading-mask"></div>'
    ),

    /** @property */
    header: OroApp.DatagridHeader,

    /**
     * Initialize datagrid
     *
     * @param {Object} options
     */
    initialize: function(options) {
        this.collection = options.collection;

        this.collection.on('request', function() {
            if (this.loadingMask) {
                this.loadingMask.show();
            }
        }, this);

        this.collection.on('sync', function() {
            if (this.loadingMask) {
                this.loadingMask.hide();
            }
        }, this);

        Backgrid.Grid.prototype.initialize.apply(this, arguments);
    },

    /**
     * Renders the grid and loading mask
     */
    render: function () {
        this.$el.empty();

        this.$el = this.$el.append($(this.template()));

        this._renderGrid(this.$('.grid'));
        this._renderLoadingMask(this.$('.loading-mask'));

        /**
         * Backbone event. Fired when the grid has been successfully rendered.
         * @event rendered
         */
        this.trigger("rendered");

        return this;
    },

    /**
     * Renders the grid's header, then footer, then finally the body.
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
     */
    _renderLoadingMask: function($el) {
        this.loadingMask = new OroApp.LoadingMask({
            el: $el
        }).render();
    }
});
