/**
 * Action launcher implemented as simple link. Click on link triggers action run
 *
 * Events:
 * click: Fired when launcher was clicked
 *
 * @class   OroApp.DatagridActionLauncher
 * @extends Backbone.View
 */
OroApp.DatagridActionLauncher = Backbone.View.extend({
    /** @property {String} */
    tagName: 'a',

    /** @property {Boolean} */
    onClickReturnValue: true,

    /** @property {OroApp.DatagridAction} */
    action: undefined,

    /** @property {String} */
    label: undefined,

    /** @property {String} */
    icon: undefined,

    /** @property {String} */
    link: 'javascript:void(0);',

    /** @property {String} */
    runAction: true,

    /** @property {function(Object, ?Object=): String} */
    template:_.template(
        '<a href="<%= link %>" class="action" ' +
            '<%= attributesTemplate({attributes: attributes}) %> ' +
            'title="<%= label %>"' +
        '>' +
            '<% if (icon) { %>' +
                '<i class="icon-<%= icon %> hide-text"><%= label %></i>' +
            '<% } else { %>' +
                '<%= label %>' +
            '<% } %>' +
        '</a>'
    ),

    attributesTemplate: _.template(
        '<% _.each(attributes, function(attribute, name) { %>' +
            '<%= name %>="<%= attribute %>" ' +
        '<% }) %>'
    ),

    /** @property */
    events: {
        'click': 'onClick'
    },

    /**
     * Initialize
     *
     * @param {Object} options
     * @param {OroApp.DatagridAction} options.action
     * @param {function(Object, ?Object=): string} [options.template]
     * @param {String} [options.label]
     * @param {String} [options.icon]
     * @param {String} [options.link]
     * @param {Boolean} [options.runAction]
     * @param {Boolean} [options.onClickReturnValue]
     * @throws {TypeError} If mandatory option is undefined
     */
    initialize: function(options) {
        options = options || {};
        if (!options.action) {
            throw new TypeError("'action' is required");
        }

        if (options.template) {
            this.template = options.template;
        }

        if (options.label) {
            this.label = options.label;
        }

        if (options.icon) {
            this.icon = options.icon;
        }

        if (options.link) {
            this.link = options.link;
        }

        if (_.has(options, 'runAction')) {
            this.runAction = options.runAction;
        }

        if (_.has(options, 'onClickReturnValue')) {
            this.onClickReturnValue = options.onClickReturnValue;
        }

        this.action = options.action;
        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Render actions
     *
     * @return {*}
     */
    render: function () {
        this.$el.empty();

        var $el = $(this.template({
            label: this.label || this.action.label,
            icon: this.icon,
            link: this.link,
            action: this.action,
            attributes: this.attributes,
            attributesTemplate: this.attributesTemplate
        }));

        this.setElement($el);
        return this;
    },

    /**
     * Handle launcher click
     *
     * @protected
     * @return {Boolean}
     */
    onClick: function() {
        this.trigger('click', this);
        if (this.runAction) {
            this.action.run();
        }
        return this.onClickReturnValue;
    }
});
