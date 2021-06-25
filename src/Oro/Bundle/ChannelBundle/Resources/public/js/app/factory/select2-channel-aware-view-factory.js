define([
    'underscore'
], function(_) {
    'use strict';

    return function(BaseSelect2View) {
        const Select2ChannelAwareView = BaseSelect2View.extend({
            $channelSelector: null,

            requiredOptions: [
                '$channelSelector',
                'additionalParamsCb'
            ],

            /**
             * @inheritdoc
             */
            constructor: function Select2ChannelAwareView(options) {
                Select2ChannelAwareView.__super__.constructor.call(this, options);
            },

            /**
             * @inheritdoc
             */
            initialize: function(options) {
                Select2ChannelAwareView.__super__.initialize.call(this, options);

                _.each(this.requiredOptions, function(optionName) {
                    if (!_.has(options, optionName)) {
                        throw new Error('Required option "' + optionName + '" not found.');
                    }
                });

                const updateData = initialCall => {
                    initialCall = initialCall || false;
                    this.$el.data('select2_query_additional_params', options.additionalParamsCb());

                    if (!initialCall) {
                        this.$el.val(null).change();
                    }
                };

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

                Select2ChannelAwareView.__super__.dispose.call(this);
            }
        });

        return Select2ChannelAwareView;
    };
});
