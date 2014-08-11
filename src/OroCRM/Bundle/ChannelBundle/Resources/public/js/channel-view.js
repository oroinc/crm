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
             * Array of fields that should be submitted for form update
             * Depends on what exact field changed
             */
            fieldsSets: {
                name:        [],
                channelType: []
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

                _.extend(this.fieldsSets, options.fieldsSets);
                this.$channelTypeEl = $(options.channelTypeEl);
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
                    title:   __('orocrm.channel.change_type'),
                    okText:  __('Yes, I Agree'),
                    content: __('orocrm.channel.submit')
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
            processChange: function() {
                var $form = $(this.options.formSelector),
                    data  = $form.serializeArray(),
                    url   = $form.attr('action'),
                    elementNames = [];

                $.each(this.options.fieldsSets,  function(key, value) {
                    elementNames.push($('#' + value).attr('name'));
                });

                var nd = _.pick(data, _.keys(elementNames)),
                    newDataArray = $.map(nd, function(value, index) {
                    return [value];
                });

                newDataArray.push({name: 'orocrm_channel_form[_token]', value: $('#orocrm_channel_form__token').val()});
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
