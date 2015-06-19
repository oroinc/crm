define(function (require) {
    'use strict';
    var Select2AutocompleteChannelAwareComponent,
        $ = require('jquery'),
        _ = require('underscore'),
        Select2AutocompleteComponent = require('oro/select2-autocomplete-component');
    Select2AutocompleteChannelAwareComponent = Select2AutocompleteComponent.extend({
        channelId: '',
        channelFieldName: '',
        initialize: function (options) {
            this.channelId = _.result(options, 'channel_id') || this.channelId;
            this.channelFieldName = _.result(options, 'channel_field_name') || this.channelFieldName;
            Select2AutocompleteChannelAwareComponent.__super__.initialize.call(this, options);
        },
        makeQuery: function (query) {
            var $channel = $('select[name="' + this.channelFieldName + '"]');
            return query + ';' + (this.channelId || $channel.val());
        }
    });
    return Select2AutocompleteChannelAwareComponent;
});

