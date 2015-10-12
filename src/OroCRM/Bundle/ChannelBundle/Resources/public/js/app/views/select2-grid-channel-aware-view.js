define([
    'jquery',
    'underscore',
    'oroform/js/app/views/select2-view'
], function($, _, Select2View) {
    'use strict';

    var Select2GridChannelAwareView = Select2View.extend({
        watchChannelParams: function(channelSelector, additionalParamsCb) {
            var updateData = _.bind(function() {
                this.$el.data('select2_query_additional_params', additionalParamsCb());
            }, this);

            $(channelSelector).on('change' + this.eventNamespace(), updateData);
            updateData();
        }
    });

    return Select2GridChannelAwareView;
});
