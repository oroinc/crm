var OroApp = OroApp || {};
OroApp.Filter = OroApp.Filter || {};

/**
 * Number filter: formats value as a number
 *
 * @class   OroApp.Filter.NumberFilter
 * @extends OroApp.Filter.ChoiceFilter
 */
OroApp.Filter.NumberFilter = OroApp.Filter.ChoiceFilter.extend({
    /** @property {OroApp.Filter.NumberFormatter} */
    formatter: new OroApp.Filter.NumberFormatter(),

    /**
     * Initialize.
     *
     * @param {Object} options
     * @param {*} [options.formatter] Object with methods fromRaw and toRaw or a string name of formatter (e.g. "integer", "decimal")
     */
    initialize: function(options) {
        options = options || {};
        this.formatter = this._getNumberFormatter(options.formatter || this.formatter);
        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * @inheritDoc
     */
    _formatRawValue: function(value) {
        if (value.value === '') {
            value.value = undefined;
        } else {
            value.value = this.formatter.toRaw(String(value.value));
        }
        return value;
    },

    /**
     * @inheritDoc
     */
    _formatDisplayValue: function(value) {
        if (_.isNumber(value.value)) {
            value.value = this.formatter.fromRaw(value.value);
        }
        return value;
    },

    /**
     * Gets instance of formatter, an object with methods "toRaw" and "fromRaw"
     *
     * @param {*} formatter
     * @return {*}
     * @protected
     */
    _getNumberFormatter: function(formatter) {
        var result = formatter;
        switch (formatter) {
            case 'integer':
                result = new OroApp.Filter.NumberFormatter({
                    decimals: 0,
                    orderSeparator: ''
                });
                break;
            case 'decimal':
                result = new OroApp.Filter.NumberFormatter();
                break;
        }
        if (_.isString(result)) {
            throw new TypeError('Cannot create formatter ' + result);
        }
        return result;
    }
});
