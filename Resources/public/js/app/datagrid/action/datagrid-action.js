/**
 * Basic action class. Subclasses should override execute method which is invoked when action is running.
 *
 * @class   OroApp.DatagridAction
 * @extends Backbone.View
 */
OroApp.DatagridAction = Backbone.View.extend({
    /** @property {Backbone.Model} */
    model: null,

    /** @property {Function} */
    launcherPrototype: OroApp.DatagridActionLauncher,

    /** @property {Object} */
    launcherOptions: undefined,

    /** @property {String} */
    link: undefined,

    /**
     * Initialize view
     *
     * @param {Object} options
     * @param {Backbone.Model} [options.model] Optional parameter
     * @throws {TypeError} If model is undefined
     */
    initialize: function(options) {
        options = options || {};

        if (!options.model) {
            throw new TypeError("'model' is required");
        }
        this.model = options.model;

        if (options.launcherOptions) {
            _.extend(this.launcherOptions, options.launcherOptions);
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
     * @return {OroApp.DatagridActionLauncher}
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
        //console.log('Execute action');
    },

    /**
     * Get action link
     *
     * @return {String}
     * @throws {TypeError} If route is undefined
     */
    getLink: function() {
        if (!this.link) {
            throw new TypeError("'link' is required");
        }

        if (this.model.has(this.link)) {
            return this.model.get(this.link);
        } else {
            return this.link;
        }
    }
});
