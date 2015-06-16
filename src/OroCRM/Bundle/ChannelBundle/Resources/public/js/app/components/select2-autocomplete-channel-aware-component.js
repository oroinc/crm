define(function (require) {
    'use strict';
    var Select2AutocompleteChannelAwareComponent,
        $ = require('jquery'),
        Select2AutocompleteComponent = require('oroform/js/app/components/select2-autocomplete-component');
    Select2AutocompleteChannelAwareComponent = Select2AutocompleteComponent.extend({
        processExtraConfig: function (select2Config, params) {
            Select2AutocompleteChannelAwareComponent.__super__.processExtraConfig(select2Config, params);
            var $channel = $('select[name="' + params.channelFieldName + '"]');
            select2Config.ajax.data = function (query, page) {
                var queryString = query + ';' + (params.channelId || $channel.val());
                return {
                    page: page,
                    per_page: params.perPage,
                    name: select2Config.autocomplete_alias,
                    query: queryString
                };
            };
            return select2Config;
        }
    });
    return Select2AutocompleteChannelAwareComponent;
});

