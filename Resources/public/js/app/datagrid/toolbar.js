var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};

/**
 * Datagrid toolbar widget
 *
 * @class   OroApp.Datagrid.Toolbar
 * @extends OroApp.View
 */
OroApp.Datagrid.Toolbar = OroApp.View.extend({

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
            '<div class="actions-panel pull-right form-horizontal"></div>' +
            '<div class="page-size pull-right form-horizontal"></div>' +
            '<div class="pagination pagination-centered"></div>' +
        '</div>'
    ),

    /** @property */
    pagination: OroApp.Datagrid.Pagination.Input,

    /** @property */
    pageSize: OroApp.Datagrid.PageSize,

    /** @property */
    actionsPanel: OroApp.Datagrid.ActionsPanel,

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

        this.actionsPanel = new this.actionsPanel({
            actions: [
                new OroApp.Datagrid.Action.RefreshCollectionAction({
                    collection: this.collection,
                    launcherOptions: {
                        label: 'Refresh',
                        className: 'btn',
                        iconClassName: 'icon-refresh'
                    }
                }),
                new OroApp.Datagrid.Action.ResetCollectionAction({
                    collection: this.collection,
                    launcherOptions: {
                        label: 'Reset',
                        className: 'btn',
                        iconClassName: 'icon-repeat'
                    }
                })
            ]
        });

        OroApp.View.prototype.initialize.call(this, options);
    },

    // actionspanel

    /**
     * Enable toolbar
     *
     * @return {*}
     */
    enable: function() {
        this.pagination.enable();
        this.pageSize.enable();
        this.actionsPanel.enable();
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
        this.actionsPanel.disable();
        return this;
    },

    /**
     * Render toolbar with pager and other views
     */
    render: function() {
        this.$el.empty();
        this.$el.append(this.template());

        this.$('.pagination').replaceWith(this.pagination.render().$el);
        this.$('.page-size').append(this.pageSize.render().$el);
        this.$('.actions-panel').append(this.actionsPanel.render().$el);

        return this;
    }
});
