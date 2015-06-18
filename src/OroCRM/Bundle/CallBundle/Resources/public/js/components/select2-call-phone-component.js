define(function (require) {
    'use strict';
    var Select2CallPhoneComponent,
        $ = require('jquery'),
        _ = require('underscore'),
        Select2Component = require('oroform/js/app/components/select2-component');
    Select2CallPhoneComponent = Select2Component.extend({
        processExtraConfig: function (select2Config, params) {
            Select2CallPhoneComponent.__super__.processExtraConfig(select2Config, params);
            select2Config.minimumResultsForSearch = 0;
            if (params.value !== false) {
                select2Config.initSelection = function (element, callback) {
                    var val = params.$el.val();
                    callback({id: val, text: val});
                };
            }
            select2Config.query = function (options) {
                var data = {results: []},
                    items = params.suggestions,
                    initialVal = $.trim(params.value),
                    currentVal = $.trim(options.element.val()),
                    term = $.trim(options.term);
                if (initialVal && _.indexOf(items, initialVal) === -1) {
                    items.unshift(initialVal);
                }
                if (currentVal && _.indexOf(items, currentVal) === -1) {
                    items.unshift(currentVal);
                }
                if (term && _.indexOf(items, term) === -1) {
                    items.unshift(term);
                }
                _.each(items, function (item) {
                    data.results.push({id: item, text: item});
                });
                options.callback(data);
            };

            return select2Config;
        }
    });
    return Select2CallPhoneComponent;
});
