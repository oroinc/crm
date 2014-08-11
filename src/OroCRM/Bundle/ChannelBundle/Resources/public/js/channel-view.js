define(
    [
        'underscore',
        'orotranslation/js/translator',
        'backbone',
        'jquery',
        'oroui/js/mediator',
        'oroui/js/delete-confirmation',

    ],
    function (_, __, Backbone, $, mediator, DeleteConfirmation) {
        'use strict';

        /**
         * @export  orocrmchannel/js/channel-view
         * @class   orocrmchannel.ChannelView
         * @extends Backbone.View
         */
        return Backbone.View.extend({

            /**
             * @type {jQuery}
             */
            $customerIdentity: null,

            /**
             * Array of fields that should be submitted for form update
             * Depends on what exact field changed
             */
            fields: {
                name: null,
                channelType: null
            },

            /**
             * Store items from `entities` field
             */
            items: [],

            /**
             * Initialize.
             *
             * @param {Object} options
             */
            initialize: function (options) {
                this.options = _.defaults(options || {}, this.options);

                if (!(options.channelTypeEl && options.formSelector)) {
                    throw new TypeError('Missing required options for ChannelView');
                }

                _.extend(this.fields, options.fields);
                this.$channelTypeEl = $(options.channelTypeEl);
                this.$tokenEl = $(options.tokenEl);
                this.$channelEntitiesEl = $(options.channelEntitiesEl);
                this.$customerIdentity = $(options.fields.customerIdentity);

                $(options.channelTypeEl).on('change', _.bind(this.changeTypeHandler, this));
                $(this.fields.entities).on('change', _.bind(this.entitiesHandler, this));


            },



        });
    }
);
