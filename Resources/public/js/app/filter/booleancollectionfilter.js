var Oro = Oro || {};
Oro.Filter = Oro.Filter || {};

/**
 * Select filter: filter value as select option
 *
 * @class   Oro.Filter.SelectFilter
 * @extends Oro.Filter.AbstractFilter
 */
Oro.Filter.BooleanCollectionFilter = Oro.Filter.SelectFilter.extend({
    /**
     * Filter value object
     *
     * @property
     */
    emptyValue: {
        value: '',
        data_in: [],
        data_not_in: []
    },

    /**
     * @inheritDoc
     */
    _readDOMValue: function() {
        return {
            value: this._getInputValue(this.inputSelector),
            data_in: this.value.data_in,
            data_not_in: this.value.data_not_in
        }
    },

    /**
     * Set included elements
     *
     * @param {Array} data
     */
    setDataIn: function(data) {
        this.value.data_in = data;
    },

    /**
     * Set excluded elements
     *
     * @param {Array} data
     */
    setDataNotIn: function(data) {
        this.value.data_not_in = data;
    }
});
