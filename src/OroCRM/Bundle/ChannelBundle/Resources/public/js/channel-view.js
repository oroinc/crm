define(['jquery', 'underscore', 'backbone', 'oroui/js/mediator', 'oroui/js/delete-confirmation', 'orotranslation/js/translator'],
    function ($, _, Backbone, mediator, DeleteConfirmation, __) {
        'use strict';

        var $ = Backbone.$;

        /**
         * @export  orocrmchannel/js/channel-view
         * @class   orocrmchannel.ChannelView
         * @extends Backbone.View
         */
        return Backbone.View.extend({

            /**
             * @const
             */
            UPDATE_MARKER: 'formUpdateMarker',

            /**
             * @type {jQuery}
             */
            $channelTypeEl: null,

            /**
             * @type {jQuery}
             */
            $tokenEl: null,

            /**
             * Array of fields that should be submitted for form update
             * Depends on what exact field changed
             */
            fields: {
                name: null,
                channelType: null
            },

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
                this.$channelTypeEl.on('change', _.bind(this.changeHandler, this));
            },

            /**
             * Open modal window
             *
             * @param e
             */
            changeHandler: function (e) {

                var prevEl = e.removed;

                var confirm = new DeleteConfirmation({
                    title: __('orocrm.channel.confirmation.change_type'),
                    okText: __('orocrm.channel.confirmation.agree'),
                    content: __('orocrm.channel.confirmation.submit')
                });

                confirm.on('ok', _.bind(function () {
                    this.processChange(this.$channelTypeEl);
                }, this));

                confirm.on('cancel', _.bind(function () {
                    this.$channelTypeEl.select2('val', prevEl.id)
                }, this));

                confirm.open();
            },

            /**
             * Reload form on change form type
             */
            processChange: function () {
                var $form = $(this.options.formSelector),
                    data = $form.serializeArray(),
                    url = $form.attr('action'),
                    elementNames = [];

                $.each(this.options.fields, function (key, value) {
                    elementNames.push($(value).attr('name'));
                });

                var nd = _.pick(data, _.keys(elementNames)),
                    newDataArray = $.map(nd, function (value, index) {
                        return [value];
                    });

                newDataArray.push({name: this.$tokenEl.name, value: this.$tokenEl.value});
                newDataArray.push({name: this.UPDATE_MARKER, value: 1});

                var event = { formEl: $form, data: data, reloadManually: true };
                mediator.trigger('channelViewFormReload:before', event);

                if (event.reloadManually) {
                    mediator.execute('submitPage', {url: url, type: $form.attr('method'), data: $.param(data)});
                }
            }
        });
    }
);
