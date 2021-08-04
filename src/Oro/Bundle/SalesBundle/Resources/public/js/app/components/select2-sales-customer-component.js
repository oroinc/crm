define(function(require) {
    'use strict';

    const _ = require('underscore');
    const Select2AutocompleteComponent = require('oro/select2-autocomplete-component');

    const Select2SalesCustomerComponent = Select2AutocompleteComponent.extend({
        /**
         * @inheritdoc
         */
        constructor: function Select2SalesCustomerComponent(options) {
            Select2SalesCustomerComponent.__super__.constructor.call(this, options);
        },

        setConfig: function(config) {
            config.formatContext = function() {
                return {
                    account: config.accountLabel
                };
            };
            config = Select2SalesCustomerComponent.__super__.setConfig.call(this, config);
            if (config.createSearchChoice) {
                config.createSearchChoice = _.wrap(config.createSearchChoice, function(original, value) {
                    const result = original(value);
                    result.icon = config.newAccountIcon || {};

                    return result;
                });
            }
            return config;
        }
    });

    return Select2SalesCustomerComponent;
});
