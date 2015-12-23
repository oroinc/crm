define([
    'underscore'
], function(_) {
    'use strict';

    return function(BaseSelect2View) {
        var Select2ChannelAwareView = BaseSelect2View.extend({
            $channelSelector: null,

            requiredOptions: [
                '$channelSelector',
                'additionalParamsCb'
            ],

            initialize: function(options) {
                Select2ChannelAwareView.__super__.initialize.apply(this, arguments);

                _.each(this.requiredOptions, function(optionName) {
                    if (!_.has(options, optionName)) {
                        throw new Error('Required option "' + optionName + '" not found.');
                    }
                });

                var updateData = _.bind(function(initialCall) {
                    initialCall = initialCall || false;
                    this.$el.data('select2_query_additional_params', options.additionalParamsCb());

                    if (!initialCall) {
                        this.$el.val(null).change();
                    }
                }, this);

                this.$channelSelector = options.$channelSelector;
                this.$channelSelector.on('change' + this.eventNamespace(), updateData);
                updateData(true);
            },

            dispose: function() {
                if (this.disposed) {
                    return;
                }

                this.$channelSelector.off(this.eventNamespace());
                delete this.$channelSelector;

                Select2ChannelAwareView.__super__.dispose.apply(this, arguments);
            }
        });

        return Select2ChannelAwareView;
    };
});
