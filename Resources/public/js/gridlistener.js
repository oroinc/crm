var Oro = Oro || {};
Oro.User = Oro.User || {};

/**
 * Listener for user role/group forms and datagrids
 *
 * @class   Oro.User.GridListener
 * @extends Oro.Datagrid.Listener.AbstractListener
 */
Oro.User.GridListener = Oro.Datagrid.Listener.AbstractListener.extend({

    /** @param {Object} */
    selectors: {
        included: null,
        excluded: null
    },

    /**
     * Initialize listener object
     *
     * @param {Object} options
     */
    initialize: function(options) {
        if (!_.has(options, 'selectors')) {
            throw new Error('Field selectors is not specified');
        }
        this.selectors = options.selectors;

        Oro.Datagrid.Listener.AbstractListener.prototype.initialize.apply(this, arguments);
    },

    /**
     * Process value
     *
     * @param {*} value
     * @param {Backbone.Model} model
     * @protected
     */
    _processValue: function(value, model) {
        var includedValues = this.get('included');
        var excludedValues = this.get('excluded');

        var isActive = model.get(this.columnName);
        if (isActive) {
            includedValues = _.union(includedValues, [value]);
            excludedValues = _.without(excludedValues, value);
        } else {
            includedValues = _.without(includedValues, value);
            excludedValues = _.union(excludedValues, [value]);
        }

        this.set('included', includedValues);
        this.set('excluded', excludedValues);

        // synchronize with form
        if (this.selectors.included) {
            $(this.selectors.included).val(includedValues.join(','));
        }
        if (this.selectors.excluded) {
            $(this.selectors.excluded).val(excludedValues.join(','));
        }

        // synchronize with datagrid
        this.datagrid.setAdditionalParameter('data_in', includedValues);
        this.datagrid.setAdditionalParameter('data_not_in', excludedValues);
    }
});
