define(function(require) {
    'use strict';

    var Select2GridChannelAwareComponent;
    var $ = require('jquery');
    var _ = require('underscore');
    var Select2GridComponent = require('oro/select2-grid-component');
    var Select2View = require('orocrmchannel/js/app/views/select2-grid-channel-aware-view');

    Select2GridChannelAwareComponent = Select2GridComponent.extend({
        channelId: '',
        channelFieldName: '',
        channelSelector: '',
        gridName: '',
        ViewType: Select2View,
        initialize: function(options) {
            this.channelId = _.result(options, 'channel_id') || this.channelId;
            this.channelFieldName = _.result(options, 'channel_field_name') || this.channelFieldName;
            this.channelSelector = 'select[name="' + this.channelFieldName + '"]';
            this.gridName = options.configs.grid.name;
            Select2GridChannelAwareComponent.__super__.initialize.call(this, options);
            this.view.watchChannelParams(this.channelSelector, _.bind(this._getAdditionalParams, this));
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
        _getAdditionalParams: function () {
            var result = {};

            var $channel = $(this.channelSelector);
            var channelIds = [$channel.val()];
            if (this.channelId) {
                channelIds.push(this.channelId);
            }

            result[this.gridName + '[channelIds]'] = channelIds.join(',');

            return result;
        }
    });

    return Select2GridChannelAwareComponent;
});

