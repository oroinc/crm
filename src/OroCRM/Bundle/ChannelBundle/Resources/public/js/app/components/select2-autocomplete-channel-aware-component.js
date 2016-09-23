define(function(require) {
    'use strict';

    var Select2AutocompleteChannelAwareComponent;
    var $ = require('jquery');
    var _ = require('underscore');
    var Select2AutocompleteComponent = require('oro/select2-autocomplete-component');
    var BaseSelect2View = require('oroform/js/app/views/select2-autocomplete-view');
    var viewFactory = require('orochannel/js/app/factory/select2-channel-aware-view-factory');
    var Select2View = viewFactory(BaseSelect2View);

    Select2AutocompleteChannelAwareComponent = Select2AutocompleteComponent.extend({
        $sourceElement: null,
        channelId: '',
        channelFieldName: '',
        ViewType: Select2View,
        initialize: function(options) {
            this.$sourceElement = options._sourceElement;
            this.channelId = _.result(options, 'channel_id') || this.channelId;
            this.channelFieldName = _.result(options, 'channel_field_name') || this.channelFieldName;
            Select2AutocompleteChannelAwareComponent.__super__.initialize.call(this, options);
        },
        prepareViewOptions: function(options, config) {
            var opts = Select2AutocompleteChannelAwareComponent.__super__.prepareViewOptions.apply(this, arguments);
            opts.$channelSelector = this.findChannelSelectorElement();
            opts.additionalParamsCb = _.bind(this._getAdditionalParams, this);

            return opts;
        },
        makeQuery: function(query) {
            var $channel = $('select[name="' + this.channelFieldName + '"]');
            return query + ';' + (this.channelId || $channel.val());
        },
        findChannelSelectorElement: function() {
            return this.$sourceElement.closest('form').find('select[name="' + this.channelFieldName + '"]');
        },
        _getAdditionalParams: function() {
            var params = {};

            var $channel = this.findChannelSelectorElement();
            var channelIds = [$channel.val()];
            if (this.channelId) {
                channelIds.push(this.channelId);
            }

            params.channelIds = channelIds.join(',');

            return {
                params: params
            };
        }
    });
    return Select2AutocompleteChannelAwareComponent;
});

