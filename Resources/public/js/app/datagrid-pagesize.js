OroApp.DatagridPageSize = OroApp.View.extend({
    /** @property */
    template: _.template(
        '<label class="control-label">View per page: &nbsp;</label>' +
        '<div class="btn-group">' +
            '<button data-toggle="dropdown" class="btn dropdown-toggle"><%= pageSize %><span class="caret"></span></button>' +
            '<ul class="dropdown-menu pull-right">' +
                '<% _.each(items, function (item) { %>' +
                    '<li><a href="#"><%= item %></a></li>' +
                '<% }); %>' +
            '</ul>' +
        '</div>'
    ),

    /** @property */
    events: {
        "click a": "changePageSize"
    },

    /** @property */
    items: [10, 25, 50, 100],

    /**
     * Initializer.
     *
     * @param {Object} options
     * @param {Backbone.Collection} options.collection
     * @param {Array} options.pageSizeItems
     */
    initialize: function (options) {
        this.collection = options.collection;
        this.listenTo(this.collection, "add", this.render);
        this.listenTo(this.collection, "remove", this.render);
        this.listenTo(this.collection, "reset", this.render);
        OroApp.View.prototype.initialize.call(this, options);
    },

    /**
     * jQuery event handler for the page handlers. Goes to the right page upon clicking.
     *
     * @param {Event} e
     */
    changePageSize: function (e) {
        e.preventDefault();
        var pageSize = parseInt($(e.target).text());
        if (pageSize !== this.collection.state.pageSize) {
            this.collection.state.pageSize = pageSize;
            this.collection.fetch();
        }
    },

    render: function() {
        this.$el.empty();
        this.$el.append($(this.template({
            pageSize: this.collection.state.pageSize,
            items: this.items
        })));

        return this;
    }
});
