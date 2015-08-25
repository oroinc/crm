define(function(require) {
    'use strict';

    var Select2AutocompleteChannelAwareComponent;
    var $ = require('jquery');
    var _ = require('underscore');
    var Select2AutocompleteComponent = require('oro/select2-autocomplete-component');

    Select2AutocompleteChannelAwareComponent = Select2AutocompleteComponent.extend({
        channelId: '',
        channelFieldName: '',
        initialize: function(options) {
            this.channelId = _.result(options, 'channel_id') || this.channelId;
            this.channelFieldName = _.result(options, 'channel_field_name') || this.channelFieldName;
            Select2AutocompleteChannelAwareComponent.__super__.initialize.call(this, options);

            var that = this;
            var $channel = $('select[name="' + this.channelFieldName + '"]');

            $channel.change(function() {
                var params = {};
                var channelIds = [$(this).val()];
                if (that.channelId) {
                    channelIds.push(that.channelId);
                }

                params.channelIds = channelIds.join(',');
                options._sourceElement.data('select2_query_additional_params', {params: params});

                $(options._sourceElement).val(null).trigger('change');
            });
        },
        makeQuery: function(query) {
            var $channel = $('select[name="' + this.channelFieldName + '"]');
            return query + ';' + (this.channelId || $channel.val());
        }
    });
    return Select2AutocompleteChannelAwareComponent;
});

