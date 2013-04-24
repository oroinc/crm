var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};

/**
 * Panel with action buttons
 *
 * @class   OroApp.Datagrid.ActionsPanel
 * @extends Backbone.View
 */
OroApp.Datagrid.ActionsPanel = Backbone.View.extend({

    /** @property */
    template: _.template(
        '<div class="btn-group"></div>'
    ),

    /** @property {OroApp.Datagrid.Action.AbstractAction[]} */
    actions: [],

    /**
     * Initialize view
     *
     * @param {Object} options
     * @param {Array} options.actions List of actions
     * @throws {TypeError} If "actions" is undefined
     */
    initialize: function(options) {
        options = options || {};

        if (!options.actions) {
            throw new TypeError("'actions' is required");
        }

        this.actions = options.actions;

        this.launchers = [];
        _.each(this.actions, function(action) {
            this.launchers.push(action.createLauncher());
        }, this);

        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Renders panel
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        this.$el = this.$el.append($(this.template()));
        var $launchersList = this.$('.btn-group');

        _.each(this.launchers, function(launcher) {
            $launchersList.append(launcher.render().$el);
        });

        return this;
    },

    /**
     * Disable
     *
     * @return {*}
     */
    disable: function() {
        _.each(this.launchers, function(launcher) {
            launcher.disable();
        });

        return this;
    },

    /**
     * Enable
     *
     * @return {*}
     */
    enable: function() {
        _.each(this.launchers, function(launcher) {
            launcher.enable();
        });

        return this;
    }
});
