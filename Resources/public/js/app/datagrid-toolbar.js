OroApp.DatagridToolbar = OroApp.View.extend({

    /** @property */
    template:_.template(
        '<div class="grid-toolbar well-small">' +
            '<div class="pull-right form-horizontal">' +
                '<label class="control-label">View per page: &nbsp;</label>' +
                '<div class="btn-group">' +
                    '<button data-toggle="dropdown" class="btn dropdown-toggle">100<span class="caret"></span></button>' +
                    '<ul class="dropdown-menu pull-right">' +
                        '<li><a href="#">10</a></li>' +
                        '<li><a href="#">25</a></li>' +
                        '<li><a href="#">50</a></li>' +
                    '</ul>' +
                '</div>' +
            '</div>' +
            '<div class="pagination pagination-centered"></div>' +
        '</div>'
    ),

    /** @property */
    pagination: OroApp.DatagridPagination,

    /**
     * Initializer.
     *
     * @param {Object} options
     * @param {Backbone.Collection} options.collection
     * @param {Integer} options.windowSize
     */
    initialize: function (options) {
        this.collection = options.collection;
        this.pagination = new this.pagination({
            collection: this.collection
        });
        OroApp.View.prototype.initialize.call(this, options);
    },

    /**
     * Render toolbar with pager and other views
     */
    render: function() {
        this.$el.empty();

        this.$el.append(this.template());
        this.$('.pagination').append(this.pagination.render().$el);

        return this;
    }
});
