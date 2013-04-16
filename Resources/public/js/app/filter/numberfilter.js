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

    /** @property {Object} */
    formatterOptions: {},

    /**
     * Initialize.
     *
     * @param {Object} options
     * @param {*} [options.formatter] Object with methods fromRaw and toRaw or a string name of formatter (e.g. "integer", "decimal")
     */
    initialize: function(options) {
        options = options || {};
        this.formatter = new OroApp.Filter.NumberFormatter(this.formatterOptions);
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
    }
});
