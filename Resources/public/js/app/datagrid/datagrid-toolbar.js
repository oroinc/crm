/**
 * Datagrid toolbar widget
 *
 * @class   OroApp.DatagridToolbar
 * @extends OroApp.View
 */
OroApp.DatagridToolbar = OroApp.View.extend({

    /** @property */
    template:_.template(
        '<div class="grid-toolbar well-small">' +
            '<div class="pull-left">' +
                '<div class="btn-group icons-holder" style="display: none;">' +
                    '<button class="btn"><i class="icon-edit hide-text">edit</i></button>' +
                    '<button class="btn"><i class="icon-copy hide-text">copy</i></button>' +
                    '<button class="btn"><i class="icon-trash hide-text">remove</i></button>' +
                '</div>' +
                '<div class="btn-group" style="display: none;">' +
                    '<button data-toggle="dropdown" class="btn dropdown-toggle">Status: <strong>All</strong><span class="caret"></span></button>' +
                    '<ul class="dropdown-menu">' +
                        '<li><a href="#">only short</a></li>' +
                        '<li><a href="#">this is long text for test</a></li>' +
                    '</ul>' +
                '</div>' +
            '</div>' +
            '<div class="page-size pull-right form-horizontal"></div>' +
            '<div class="pagination pagination-centered"></div>' +
        '</div>'
    ),

    /** @property */
    pagination: OroApp.DatagridPaginationInput,

    /** @property */
    pageSize: OroApp.DatagridPageSize,

    /**
     * Initializer.
     *
     * @param {Object} options
     * @param {Backbone.Collection} options.collection
     */
    initialize: function (options) {
        options = options || {};

        if (!options.collection) {
            throw new TypeError("'collection' is required");
        }

        this.collection = options.collection;

        this.pagination = new this.pagination({
            collection: this.collection
        });

        this.pageSize = new this.pageSize({
            collection: this.collection
        });

        OroApp.View.prototype.initialize.call(this, options);
    },

    /**
     * Enable toolbar
     *
     * @return {*}
     */
    enable: function() {
        this.pagination.enable();
        this.pageSize.enable();
        return this;
    },

    /**
     * Disable toolbar
     *
     * @return {*}
     */
    disable: function() {
        this.pagination.disable();
        this.pageSize.disable();
        return this;
    },

    /**
     * Render toolbar with pager and other views
     */
    render: function() {
        this.$el.empty();
        this.$el.append(this.template());

        this.$('.pagination').append(this.pagination.render().$el);
        this.$('.page-size').append(this.pageSize.render().$el);

        return this;
    }
});
