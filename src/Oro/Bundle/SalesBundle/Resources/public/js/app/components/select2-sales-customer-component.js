define(function(require) {
    'use strict';

    var _ = require('underscore');
    var Select2AutocompleteComponent = require('oro/select2-autocomplete-component');

    var Select2SalesCustomerComponent = Select2AutocompleteComponent.extend({
        setConfig: function(config) {
            config.formatContext = function() {
                return {
                    'account': config.accountLabel
                };
            };
            config = Select2SalesCustomerComponent.__super__.setConfig.apply(this, arguments);
            if (config.createSearchChoice) {
                config.createSearchChoice = _.wrap(config.createSearchChoice, function(original, value) {
                    var result = original(value);
                    result.icon = config.newAccountIcon || {};

                    return result;
                });
            }
            return config;
        }
    });

    return Select2SalesCustomerComponent;
});
