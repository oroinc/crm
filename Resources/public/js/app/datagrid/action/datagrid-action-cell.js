/**
 * Cell for grid, contains actions
 *
 * @class   OroApp.DatagridActionCell
 * @extends Backgrid.Cell
 */
OroApp.DatagridActionCell = Backgrid.Cell.extend({

    /** @property */
    className: "action-cell",

    /** @property {Array} */
    actions: undefined,

    /** @property {Array} */
    launchers: undefined,

    /** @property */
    template:_.template(
        '<div class="more-bar-holder">' +
            '<div class="dropdown">' +
                '<a data-toggle="dropdown" class="dropdown-toggle" href="#">...</a>' +
                '<ul class="dropdown-menu pull-right launchers-dropdown-menu"></ul>' +
            '</div>' +
        '</div>'
    ),

    /** @property */
    launchersListTemplate: _.template(
        '<% if (withIcons) { %>' +
            '<li><ul class="nav nav-pills icons-holder launchers-list"></ul></li>' +
        '<% } else { %>' +
            '<li class="well-small"><ul class="unstyled launchers-list"></ul></li>' +
        '<% } %>'
    ),

    /** @property */
    launcherItemTemplate:_.template(
        '<li class="launcher-item"></li>'
    ),

    /**
     * Initilize cell actions and launchers
     */
    initialize: function() {
        Backgrid.Cell.prototype.initialize.apply(this, arguments);
        this.actions = this.createActions();
        _.each(this.actions, function(action) {
            action.on('run', this.onActionRun, this);
        }, this)

        this.launchers = this.createLaunchers();
    },

    /**
     * Handle action run
     *
     * @param {OroApp.DatagridAction} action
     */
    onActionRun: function(action) {
        this.$('.dropdown.open').removeClass('open');
    },

    /**
     * Creates actions
     *
     * @return {Array}
     */
    createActions: function() {
        var result = [];
        var actions = this.column.get('actions');
        for (var index in actions) {
            result.push(this.createAction(actions[index]));
        }

        return result;
    },

    /**
     * Creates action
     *
     * @param {Function} actionPrototype
     * @protected
     */
    createAction: function(actionPrototype) {
        return new (actionPrototype)({
            model: this.model
        });
    },

    /**
     * Creates actions launchers
     *
     * @protected
     */
    createLaunchers: function() {
        var result = [];

        for (var index in this.actions) {
            var options = {};
            if (this.actions[index].icon) {
                options = {
                    icon: this.actions[index].icon
                };
            }
            result.push(this.actions[index].createLauncher(options));
        }

        return result;
    },

    /**
     * Render cell with actions
     */
    render: function () {
        this.$el.empty().append(this.template());

        var launchers = this.getLaunchersByIcons();
        var $listsContainer = this.$('.launchers-dropdown-menu');

        if (launchers.withIcons.length) {
            this.renderLaunchersList(
                launchers.withIcons, {
                    withIcons: true
                }
            ).appendTo($listsContainer);
        }

        if (launchers.withIcons.length && launchers.withoutIcons.length) {
            $listsContainer.append('<li class="divider"></li>');
        }

        if (launchers.withoutIcons.length) {
            this.renderLaunchersList(
                launchers.withoutIcons, {
                    withIcons: false
                }
            ).appendTo($listsContainer);
        }

        return this;
    },

    /**
     * Render launchers list
     *
     * @param {Array} launchers
     * @param {Object} [templateParameters]
     * @return {jQuery} Rendered element wrapped with jQuery
     */
    renderLaunchersList: function(launchers, templateParameters) {
        templateParameters = templateParameters || {};
        var result = $(this.launchersListTemplate(templateParameters));
        var $launchersList = result.filter('.launchers-list').length ? result : $('.launchers-list', result);
        _.each(launchers, function(launcher) {
            $launchersList.append(this.renderLauncherItem(launcher));
        }, this);

        return result;
    },

    /**
     * Render launcher
     *
     * @param {OroApp.DatagridActionLauncher} launcher
     * @param {Object} [templateParameters]
     * @return {jQuery} Rendered element wrapped with jQuery
     */
    renderLauncherItem: function(launcher, templateParameters) {
        templateParameters = templateParameters || {};
        var result = $(this.launcherItemTemplate(templateParameters));
        var $launcherItem = result.filter('.launcher-item').length ? result : $('.launcher-item', result);
        $launcherItem.append(launcher.render().$el);
        return result;
    },

    /**
     * Get separate object of launchers arrays: with icons (key `withIcons`) and without icons (key `withoutIcons`).
     *
     * @return {Object}
     * @protected
     */
    getLaunchersByIcons: function() {
        var result = {
            withIcons: [],
            withoutIcons: []
        };

        for (var index in this.launchers) {
            var launcher = this.launchers[index];
            if (launcher.icon) {
                result.withIcons.push(launcher);
            } else {
                result.withoutIcons.push(launcher);
            }
        }

        return result;
    }
});
