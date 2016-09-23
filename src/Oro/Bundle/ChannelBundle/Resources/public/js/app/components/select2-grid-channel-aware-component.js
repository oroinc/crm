define(function(require) {
    'use strict';

    var Select2GridChannelAwareComponent;
    var _ = require('underscore');
    var Select2GridComponent = require('oro/select2-grid-component');
    var BaseSelect2View = require('oroform/js/app/views/select2-view');
    var viewFactory = require('orochannel/js/app/factory/select2-channel-aware-view-factory');
    var Select2View = viewFactory(BaseSelect2View);

    Select2GridChannelAwareComponent = Select2GridComponent.extend({
        $sourceElement: null,
        channelId: '',
        channelFieldName: '',
        gridName: '',
        ViewType: Select2View,
        initialize: function(options) {
            this.$sourceElement = options._sourceElement;
            this.channelId = _.result(options, 'channel_id') || this.channelId;
            this.channelFieldName = _.result(options, 'channel_field_name') || this.channelFieldName;
            this.gridName = options.configs.grid.name;
            Select2GridChannelAwareComponent.__super__.initialize.call(this, options);
        },
        prepareViewOptions: function(options, config) {
            var opts = Select2GridChannelAwareComponent.__super__.prepareViewOptions.apply(this, arguments);
            opts.$channelSelector = this.findChannelSelectorElement();
            opts.additionalParamsCb = _.bind(this._getAdditionalParams, this);

            return opts;
        },
        preConfig: function(config) {
            Select2GridChannelAwareComponent.__super__.preConfig.call(this, config);

            var that = this;
            config.ajax.data = _.wrap(config.ajax.data, function(parentDataFunction) {
                var result = parentDataFunction.apply(this, _.rest(arguments));

                return _.extend(result, that._getAdditionalParams());
            });

            return config;
        },
        findChannelSelectorElement: function() {
            return this.$sourceElement.closest('form').find('select[name="' + this.channelFieldName + '"]');
        },
        _getAdditionalParams: function() {
            var result = {};
            var $channel = this.findChannelSelectorElement();
            var channelIds = [$channel.val()];

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

