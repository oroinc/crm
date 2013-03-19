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
    url: undefined,

    /** @property {Object} */
    placeholders: {},

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

        if (options.url) {
            this.url = options.url;
        }

        if (options.placeholders) {
            this.placeholders = _.extend(this.placeholders, options.placeholders);
        }

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
     * Generates url of action based on route, placeholders and model
     *
     * @return {String}
     * @throws {TypeError} If route is undefined
     */
    generateUrl: function() {
        if (!this.url) {
            throw new TypeError("'url' is required");
        }
        return this.processUrl(this.url, this.processPlaceholders(this.placeholders, this.model));
    },

    /**
     * Process url parameters
     *
     * @param {String} url
     * @param {Object} parameters Key-value storage of parameters
     * @return String
     * @protected
     */
    processUrl: function(url, parameters) {
        result = url;
        for (var name in parameters) {
            result = result.replace(name, parameters[name]);
        }
        return result;
    },

    /**
     * Replace values of `placeholders` with values of model properties
     *
     * @param {Object} placeholders
     * @param {Backbone.Model} model
     * @return {Object}
     * @protected
     */
    processPlaceholders: function(placeholders, model) {
        placeholders = placeholders || {};
        var result = {};
        _.each(placeholders, function(value, name) {
            if (model.has(value)) {
                value = model.get(value);
            }
            result[name] = value;
        }, this);

        return result;
    }
});
