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
     * Is filter enabled by default
     *
     * @property {Boolean}
     */
    defaultEnabled: false,

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
     * Value that was confirmed and processed.
     *
     * @property {Object}
     */
    confirmedValue: {},

    /**
     * Empty value object
     *
     * @property {Object}
     */
    emptyValue: {},

    /**
     * Parent element active class
     *
     * @property {String}
     */
    buttonActiveClass: 'open-filter',

    /**
     * Initialize.
     *
     * @param {Object} options
     * @param {Boolean} [options.enabled]
     */
    initialize: function(options) {
        options = options || {};
        if (_.has(options, 'enabled')) {
            this.enabled = options.enabled;
        }
        this.defaultEnabled = this.enabled;
        Backbone.View.prototype.initialize.apply(this, arguments);
    },

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
        this.setValue(this.emptyValue);
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
     * Compares current value with empty value
     *
     * @return {Boolean}
     */
    isEmpty: function() {
        return this._looseObjectCompare(this.confirmedValue, this.emptyValue);
    },

    /**
     * Compare objects
     *
     * @param {*} obj1
     * @param {*} obj2
     * @return {*}
     * @protected
     */
    _looseObjectCompare: function (obj1, obj2) {
        for (var i in obj1) {
            // both items are objects
            if (_.isObject(obj1[i]) && _.isObject(obj2[i])) {
                return this._looseObjectCompare(obj1[i], obj2[i]);
            } else {
                var equalsLoosely = (obj1[i] || '') == (obj2[i] || '');
                var eitherNumber = _.isNumber(obj1[i]) || _.isNumber(obj2[i]);
                var equalsNumbers = Number(obj1[i]) == Number(obj2[i]);
                if (!(equalsLoosely || (eitherNumber && equalsNumbers))) {
                    return false;
                }
            }
        }
        return true;
    },

    /**
     * Set filter button class
     *
     * @param {Object} element
     * @param {Boolean} status
     * @protected
     */
    _setButtonPressed: function(element, status) {
        if (status) {
            element.parent().addClass(this.buttonActiveClass);
        } else {
            element.parent().removeClass(this.buttonActiveClass);
        }
    }
});
