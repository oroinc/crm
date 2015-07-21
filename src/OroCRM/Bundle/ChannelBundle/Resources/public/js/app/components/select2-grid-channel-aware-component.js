define(function(require) {
    'use strict';

    var Select2GridChannelAwareComponent;
    var $ = require('jquery');
    var _ = require('underscore');
    var Select2GridComponent = require('oro/select2-grid-component');

    Select2GridChannelAwareComponent = Select2GridComponent.extend({
        channelId: '',
        channelFieldName: '',
        initialize: function(options) {
            this.channelId = _.result(options, 'channel_id') || this.channelId;
            this.channelFieldName = _.result(options, 'channel_field_name') || this.channelFieldName;
            Select2GridChannelAwareComponent.__super__.initialize.call(this, options);
        },
        preConfig: function(config) {
            Select2GridChannelAwareComponent.__super__.preConfig.call(this, config);
            var that = this;
            var $channel = $('select[name="' + this.channelFieldName + '"]');

            config.ajax.data = _.wrap(config.ajax.data, function(parentDataFunction) {
                var result = parentDataFunction.apply(this, _.rest(arguments));
                var channelIds = [$channel.val()];
                if (that.channelId) {
                    channelIds.push(that.channelId);
                }
                result[config.grid.name + '[channelIds]'] = channelIds.join(',');
                return result;
            });

            return config;
        }
    });
    return Select2GridChannelAwareComponent;
});

