/**
 * Datagrid title block
 *
 * @class   OroApp.DatagridFilterList
 * @extends Backbone.View
 */
OroApp.DatagridTitle = Backbone.View.extend({
    /** @property */
    template: _.template(
        '<div class="top-action-box">' +
            '<div class="btn-group icons-holder icons-small">' +
                '<button class="btn"><i class="icon-minimize hide-text">minimaze tab</i></button>' +
                '<button class="btn"><i class="icon-fullscreen hide-text">full screen</i></button>' +
                '<button class="btn"><i class="icon-remove hide-text">Close</i></button>' +
            '</div>' +
        '</div>' +
        '<div class="brand-extra pull-left"><%= title %></div>' +
        '<div class="divider-vertical pull-left" style="height:100%;"></div>' +
        '<div class="navbar-responsive-collapse navbar-extra clearfix">' +
            '<div class="btn-group pull-left">' +
                '<button class="btn dark">Views</button>' +
                '<button class="btn dropdown-toggle" data-toggle="dropdown">Accounts owned by me<span class="caret"></span></button>' +
                '<ul class="dropdown-menu">' +
                    '<li><a href="#">Last update: <strong>2010/12/12-2013/18/18</strong></a></li>' +
                    '<li><a href="#">Last update: <strong>2011/12/12-2014/11/18</strong></a></li>' +
                '</ul>' +
            '</div>' +
            '<div class="btn-group pull-left">' +
            '<button type="button" class="btn btn-link"> + Add View</button>' +
            '</div>' +
        '<div class="btn-group pull-right">' +
            '<button class="btn btn-primary"><i class="icon-plus icon-white"></i> New Customers</button>' +
        '</div>' +
        '<div class="btn-group pull-right">' +
            '<button class="btn btn-primary"><i class="icon-window"></i>Quick Add</button>' +
            '</div>' +
        '</div>'
    ),

    /** @property */
    title: 'Grid',

    /**
     * Initialize filter list options
     *
     * @param {Object} options
     */
    initialize: function(options)
    {
        if (options.entityHint) {
            this.title = options.entityHint;
        }

        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Render title
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        this.$el = this.$el.append(this.template({
            title: this.title
        }));

        this.trigger("rendered");

        return this;
    }
});