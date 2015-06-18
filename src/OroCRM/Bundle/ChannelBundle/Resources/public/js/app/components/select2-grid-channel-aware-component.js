define(function (require) {
    'use strict';
    var Select2GridChannelAwareComponent,
        $ = require('jquery'),
        Select2GridComponent = require('oroform/js/app/components/select2-grid-component');
    Select2GridChannelAwareComponent = Select2GridComponent.extend({
        processExtraConfig: function (select2Config, params) {
            Select2GridChannelAwareComponent.__super__.processExtraConfig(select2Config, params);
            var parentDataFunction = select2Config.ajax.data,
                $channel = $('select[name="' + params.channelFieldName + '"]');

            select2Config.ajax.data = function (query, page, searchById) {
                var result = parentDataFunction.apply(this, arguments),
                    channelIds = [$channel.val()];
                if (params.channelId) {
                    channelIds.push(params.channelId);
                }
                result[select2Config.grid.name + '[channelIds]'] = channelIds.join(',');
                return result;
            };


            return select2Config;
        }
    });
    return Select2GridChannelAwareComponent;
});

