define([
    'jquery',
    'underscore',
    'oroform/js/app/views/select2-view'
], function($, _, Select2View) {
    'use strict';

    var Select2GridChannelAwareView = Select2View.extend({
        $channelSelector: null,

        requiredOptions: [
            '$channelSelector',
            'additionalParamsCb'
        ],

        initialize: function(options) {
            Select2GridChannelAwareView.__super__.initialize.apply(this, arguments);

            _.each(this.requiredOptions, function(optionName) {
                if (!_.has(options, optionName)) {
                    throw new Error('Required option "' + optionName + '" not found.');
                }
            });

            var updateData = _.bind(function() {
                this.$el.data('select2_query_additional_params', options.additionalParamsCb());
            }, this);

            this.$channelSelector = options.$channelSelector;
            this.$channelSelector.on('change' + this.eventNamespace(), updateData);
            updateData();
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$channelSelector.off(this.eventNamespace());
            delete this.$channelSelector;

            Select2View.__super__.dispose.apply(this, arguments);
        }
    });

    return Select2GridChannelAwareView;
});
