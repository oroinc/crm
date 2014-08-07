define(['underscore', 'orotranslation/js/translator', 'backbone', 'oroui/js/mediator', 'jquery.select2'],
    function (_, __, Backbone, mediator) {
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
            $channelEntitiesEl: null,

            /**
             * @type {jQuery}
             */
            $customerIdentity: null,

            /**
             * Array of fields that should be submitted for form update
             * Depends on what exact field changed
             */
            fieldsSets: {
                name:        [],
                channelType: []
            },

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

                _.extend(this.fieldsSets, options.fieldsSets);
                this.$channelTypeEl = $(options.channelTypeEl);
                this.$channelEntitiesEl = $(options.channelEntitiesEl);
                this.$customerIdentity = $('#'+options.fieldsSets.customerIdentity);

                $(options.channelTypeEl).on('change', _.bind(this.changeHandler, this));
                $('#'+this.fieldsSets.entities).on('change', _.bind(this.entitiesHandler, this));
                this.initSelect2(options.fieldsSets.customerIdentity);

                if ([].length  === this.items.length) {
                    this.entitiesHandler();
                    this.setCustomerIdentityValue(this.$customerIdentity.val());
                }
            },

            /**
             * This is listening changes in channel type field and submit form
             */
            changeHandler: function () {
                var $form = $(this.options.formSelector),
                    data = $form.serializeArray(),
                    url = $form.attr('action');

                data.push({name: this.UPDATE_MARKER, value: 1});
                var event = { formEl: $form, data: data, reloadManually: true };

                mediator.trigger('channelViewFormReload:before', event);

                if (event.reloadManually) {
                    mediator.execute('submitPage', {url: url, type: $form.attr('method'), data: $.param(data)});
                }
            },

            /**
             * This is listening changes in `Entities` field and fill `Customer identity` field
             */
            entitiesHandler: function () {
                var selectedEntities = this.$channelEntitiesEl.find('option:selected'),
                    self = this;
                this.items = [];

                $.each(selectedEntities, function(key, value) {
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
            validateSelect2: function() {
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
                $('#' + id).select2({
                    placeholder: __('orocrm.channel.form.select_customer_identity'),
                    data: function () {
                        return self.data();
                    }
                });
            },

            /**
             * Walk by `this.items` and add it to `data`
             *
             * @returns Object
             */
            data: function() {
                var data = {
                    more: false,
                    results: []
                };

                $.each(this.items, function(key, value) {
                    data.results.push(value);
                });

                return data;
            },

            /**
             *
             * @param value
             */
            setCustomerIdentityValue: function(value) {
                var self = this;

                $.each(this.items, function(key, va) {
                    if (_.findWhere(self.items, {id: va})) {
                        console.log(this);
                    }
                });

                this.$customerIdentity.select2('val', value);
            }
        });
    }
);
