var OroApp = OroApp || {};
OroApp.Datagrid = OroApp.Datagrid || {};
OroApp.Datagrid.Action = OroApp.Datagrid.Action || {};

/**
 * Abstract action class. Subclasses should override execute method which is invoked when action is running.
 *
 * @class   OroApp.Datagrid.Action.AbstractAction
 * @extends Backbone.View
 */
OroApp.Datagrid.Action.AbstractAction = Backbone.View.extend({
    /** @property {Function} */
    launcherPrototype: OroApp.Datagrid.Action.Launcher,

    /** @property {Object} */
    launcherOptions: undefined,

    /**
     * Initialize view
     *
     * @param {Object} options
     * @param {Object} [options.launcherOptions] Options for new instance of launcher object
     */
    initialize: function(options) {
        options = options || {};

        if (options.launcherOptions) {
            this.launcherOptions = _.extend({}, this.launcherOptions, options.launcherOptions);
        }

        this.launcherOptions = _.extend({
            action: this
        }, this.launcherOptions);

        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Creates launcher
     *
     * @param {Object} options Launcher options
     * @return {OroApp.Datagrid.Action.Launcher}
     */
    createLauncher: function(options) {
        options = options || {};
        _.defaults(options, this.launcherOptions);
        return new (this.launcherPrototype)(options);
    },

    /**
     * Run action
     */
    run: function() {
        this.execute();
    },

    /**
     * Execute action
     */
    execute: function() {
        throw new Error("Method execute is abstract and must be implemented");
    }
});
