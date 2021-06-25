define(function(require) {
    'use strict';

    const _ = require('underscore');
    const Select2GridComponent = require('oro/select2-grid-component');
    const BaseSelect2View = require('oroform/js/app/views/select2-view');
    const viewFactory = require('orochannel/js/app/factory/select2-channel-aware-view-factory');
    const Select2View = viewFactory(BaseSelect2View);

    const Select2GridChannelAwareComponent = Select2GridComponent.extend({
        $sourceElement: null,
        channelId: '',
        channelFieldName: '',
        gridName: '',
        ViewType: Select2View,
        /**
         * @inheritdoc
         */
        constructor: function Select2GridChannelAwareComponent(options) {
            Select2GridChannelAwareComponent.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            this.$sourceElement = options._sourceElement;
            this.channelId = _.result(options, 'channel_id') || this.channelId;
            this.channelFieldName = _.result(options, 'channel_field_name') || this.channelFieldName;
            this.gridName = options.configs.grid.name;
            Select2GridChannelAwareComponent.__super__.initialize.call(this, options);
        },
        prepareViewOptions: function(options, config) {
            const opts = Select2GridChannelAwareComponent.__super__.prepareViewOptions.call(this, options, config);
            opts.$channelSelector = this.findChannelSelectorElement();
            opts.additionalParamsCb = this._getAdditionalParams.bind(this);

            return opts;
        },
        preConfig: function(config) {
            Select2GridChannelAwareComponent.__super__.preConfig.call(this, config);

            const that = this;
            config.ajax.data = _.wrap(config.ajax.data, function(parentDataFunction, ...rest) {
                const result = parentDataFunction.apply(this, rest);

                return _.extend(result, that._getAdditionalParams());
            });

            return config;
        },
        findChannelSelectorElement: function() {
            return this.$sourceElement.closest('form').find('select[name="' + this.channelFieldName + '"]');
        },
        _getAdditionalParams: function() {
            const result = {};
            const $channel = this.findChannelSelectorElement();
            const channelIds = [$channel.val()];

            if (this.channelId) {
                channelIds.push(this.channelId);
            }

            result.channelIds = channelIds.join(',');

            result[this.gridName + '[channelIds]'] = channelIds.join(',');

            return result;
        }
    });

    return Select2GridChannelAwareComponent;
});

