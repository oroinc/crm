/**
 * Basic grid filter
 *
 * @class   OroApp.DatagridFilter
 * @extends Backbone.View
 */
OroApp.DatagridFilter = Backbone.View.extend({
    /**
     * Filter container tag
     *
     * @property {String}
     */
    tagName: 'div',

    /**
     * Filter container class name
     *
     * @property {String}
     */
    className: 'btn-group filter-item oro-drop',

    /**
     * Is filter enabled
     *
     * @property {Boolean}
     */
    enabled: false,

    /**
     * Name of filter field
     *
     * @property {String}
     */
    name: 'input_name',

    /**
     * Label of filter
     *
     * @property {String}
     */
    label: 'Input Label',

    /**
     * Enable filter
     *
     * @return {*}
     */
    enable: function() {
        if (!this.enabled) {
            this.enabled = true;
            this.show();
        }
        return this;
    },

    /**
     * Disable filter
     *
     * @return {*}
     */
    disable: function() {
        if (this.enabled) {
            this.enabled = false;
            this.hide();
            this.trigger('disable', this);
            this.reset();
        }
        return this;
    },

    /**
     * Show filter
     *
     * @return {*}
     */
    show: function() {
        this.$el.css('display', 'inline-block');
        return this;
    },

    /**
     * Hide filter
     *
     * @return {*}
     */
    hide: function() {
        this.$el.hide();
        return this;
    },

    /**
     * Reset filter elements
     *
     * @return {*}
     */
    reset: function() {
        return this;
    },

    /**
     * Set value to filter
     *
     * @param value
     * @return {*}
     */
    setValue: function(value) {
        return this;
    },

    /**
     * Get filter value
     *
     * @return {Object}
     */
    getValue: function() {
        return {};
    },

    /**
     * Set filter parameters
     *
     * @deprecated
     * @param {Object} parameters
     * @return {*}
     */
    setParameters: function(parameters) {
        return this;
    },

    /**
     * Get filter parameters
     *
     * @deprecated
     * @return {Object}
     */
    getParameters: function() {
        return {};
    }
});
