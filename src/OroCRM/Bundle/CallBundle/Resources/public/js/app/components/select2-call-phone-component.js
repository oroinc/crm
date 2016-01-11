define(function(require) {
    'use strict';

    var Select2CallPhoneComponent;
    var $ = require('jquery');
    var _ = require('underscore');
    var Select2Component = require('oro/select2-component');

    Select2CallPhoneComponent = Select2Component.extend({
        suggestions: [],
        value: '',
        initialize: function(options) {
            this.suggestions = _.result(options, 'suggestions') || this.suggestions;
            this.value = _.result(options, 'value') || this.value;
            Select2CallPhoneComponent.__super__.initialize.call(this, options);
        },
        preConfig: function(config) {
            var that = this;
            Select2CallPhoneComponent.__super__.preConfig.call(this, config);
            config.minimumResultsForSearch = 0;
            if (this.value !== false) {
                config.initSelection = function(element, callback) {
                    var val = element.val();
                    callback({id: val, text: val});
                };
            }
            config.query = function(options) {
                var data = {results: []};
                var items = _.clone(that.suggestions);
                var initialVal = $.trim(that.value);
                var currentVal = $.trim(options.element.val());
                var term = $.trim(options.term);
                if (initialVal && _.indexOf(items, initialVal) === -1) {
                    items.unshift(initialVal);
                }
                if (currentVal && _.indexOf(items, currentVal) === -1) {
                    items.unshift(currentVal);
                }
                if (term && _.indexOf(items, term) === -1) {
                    items.unshift(term);
                }
                _.each(items, function(item) {
                    data.results.push({id: item, text: item});
                });
                options.callback(data);
            };

            return config;
        }
    });
    return Select2CallPhoneComponent;
});
