define(function(require) {
    'use strict';

    const $ = require('jquery');
    const _ = require('underscore');
    const Select2AutocompleteComponent = require('oro/select2-autocomplete-component');
    const BaseSelect2View = require('oroform/js/app/views/select2-autocomplete-view');
    const viewFactory = require('orochannel/js/app/factory/select2-channel-aware-view-factory');
    const Select2View = viewFactory(BaseSelect2View);

    const Select2AutocompleteChannelAwareComponent = Select2AutocompleteComponent.extend({
        $sourceElement: null,
        channelId: '',
        channelFieldName: '',
        ViewType: Select2View,
        /**
         * @inheritdoc
         */
        constructor: function Select2AutocompleteChannelAwareComponent(options) {
            Select2AutocompleteChannelAwareComponent.__super__.constructor.call(this, options);
        },
        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.$sourceElement = options._sourceElement;
            this.channelId = _.result(options, 'channel_id') || this.channelId;
            this.channelFieldName = _.result(options, 'channel_field_name') || this.channelFieldName;
            Select2AutocompleteChannelAwareComponent.__super__.initialize.call(this, options);
        },
        prepareViewOptions: function(options, config) {
            const opts = Select2AutocompleteChannelAwareComponent.
                __super__.prepareViewOptions.call(this, options, config);
            opts.$channelSelector = this.findChannelSelectorElement();
            opts.additionalParamsCb = this._getAdditionalParams.bind(this);

            return opts;
        },
        makeQuery: function(query) {
            const $channel = $('select[name="' + this.channelFieldName + '"]');
            return query + ';' + (this.channelId || $channel.val());
        },
        findChannelSelectorElement: function() {
            return this.$sourceElement.closest('form').find('select[name="' + this.channelFieldName + '"]');
        },
        _getAdditionalParams: function() {
            const params = {};

            const $channel = this.findChannelSelectorElement();
            const channelIds = [$channel.val()];
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

