define(['underscore', 'backbone', 'oroui/js/mediator', 'jquery.select2'],
    function (_, Backbone, mediator) {
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
                this.$channelEntitiesEl = $(options.channelEntitiesEl);

                $(options.channelTypeEl).on('change', _.bind(this.changeHandler, this));

                $('#'+this.fieldsSets.entities).on('change', _.bind(this.entitiesHandler, this));
            },

            changeHandler: function (e) {
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

            entitiesHandler: function (event) {
                var selectedEntities = this.$channelEntitiesEl.find('option:selected'),
                    customerIdentity = $('#' + this.fieldsSets.customerIdentity);

                customerIdentity.html('');

                $.each(selectedEntities, function(key, value) {
                    var jqOption = $(value),
                        newOption = new Option(jqOption.text(), jqOption.val());

                    customerIdentity.append(newOption);
                });
                customerIdentity.select2();
            }
        });
    }
);
