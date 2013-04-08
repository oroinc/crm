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
     * Loosely compare two values
     *
     * @param {*} value1
     * @param {*} value2
     * @return {Boolean} TRUE if values are equal, otherwise - FALSE
     * @protected
     */
    _looseObjectCompare: function (value1, value2) {
        if (!_.isObject(value1)) {
            var equalsLoosely = (value1 || '') == (value2 || '');
            var eitherNumber = _.isNumber(value1) || _.isNumber(value2);
            var equalsNumbers = Number(value1) == Number(value2);
            return equalsLoosely || (eitherNumber && equalsNumbers);

        } else if (_.isObject(value1)) {
            var valueKeys = _.keys(value1);

            if (_.isObject(value2)) {
                valueKeys = _.unique(valueKeys.concat(_.keys(value2)));
            }

            for (var index in valueKeys) {
                var key = valueKeys[index];
                if (!this._looseObjectCompare(value1[key], value2[key])) {
                    return false;
                }
            }
            return true;
        } else {
            return value1 == value2;
        }
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
