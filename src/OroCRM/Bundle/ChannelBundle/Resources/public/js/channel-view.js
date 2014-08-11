define(
    [
        'underscore',
        'orotranslation/js/translator',
        'backbone',
        'jquery',
        'oroui/js/mediator',
        'oroui/js/delete-confirmation',
        'jquery.select2'
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
             * @type {jQuery}
             */
            $channelEntitiesEl: null,

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

                this.initSelect2(options.fields.customerIdentity);

                if (0 === this.items.length) {
                    this.entitiesHandler();
                    this.setCustomerIdentityValue(this.$customerIdentity.val());
                }
            },

            /**
             * Open modal window
             *
             * @param e
             */
            changeTypeHandler: function (e) {
                var prevEl = e.removed,
                    confirm = new DeleteConfirmation({
                    title: __('orocrm.channel.confirmation.title'),
                    okText: __('orocrm.channel.confirmation.agree'),
                    content: __('orocrm.channel.confirmation.text')
                });

                confirm.on('ok', _.bind(function () {
                    this.processChangeType(this.$channelTypeEl);
                }, this));

                confirm.on('cancel', _.bind(function () {
                    this.$channelTypeEl.select2('val', prevEl.id)
                }, this));

                confirm.open();
            },

            /**
             * Reload form on change form type
             */
            processChangeType: function () {
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
                    mediator.execute(
                        'submitPage',
                        {url: url, type: $form.attr('method'), data: $.param(newDataArray)}
                    );
                }
            },

            /**
             * This is listening changes in `Entities` field and fill `Customer identity` field
             */
            entitiesHandler: function () {
                var selectedEntities = this.$channelEntitiesEl.find('option:selected'),
                    self = this;
                this.items = [];

                $.each(selectedEntities, function (key, value) {
                    var jqOption = $(value),
                        item = {
                            id: jqOption.val(),
                            text: jqOption.text()
                        };
                    self.items.push(item);
                });
                this.validateSelect2();
            },

            /**
             * Validate `select2` if you delete value from `Entities` field and don't delete from `Customer identity`
             * field, it will reset value in `Customer identity` field
             */
            validateSelect2: function () {
                var value = this.$customerIdentity.val();

                if (value) {
                    if (!_.findWhere(this.items, {id: value})) {
                        this.$customerIdentity.select2('val', '');
                    }
                }
            },

            /**
             * Build `select2`
             *
             * @param id
             */
            initSelect2: function (id) {
                var self = this;
                $(id).select2({
                    placeholder: __('orocrm.channel.form.select_customer_identity'),
                    data: function () {
                        return self.select2Data();
                    }
                });
            },

            /**
             * Walk by `this.items` and add it to `data`
             *
             * @returns Object
             */
            select2Data: function () {
                var data = {
                    more: false,
                    results: []
                };

                $.each(this.items, function (key, value) {
                    data.results.push(value);
                });

                return data;
            },

            /**
             * Set `Customer Identity` field value
             *
             * @param value
             */
            setCustomerIdentityValue: function (value) {
                this.$customerIdentity.select2('val', value);
            }
        });
    }
);
