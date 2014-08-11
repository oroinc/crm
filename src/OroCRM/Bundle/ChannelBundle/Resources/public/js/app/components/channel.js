define(['underscore', 'orotranslation/js/translator', 'backbone', 'oroui/js/mediator', 'oroui/js/delete-confirmation'],
function (_, __, Backbone, mediator, DeleteConfirmation) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @todo probably should not be an instance of backbone view
     * @class   orocrmchannel.app.components.Channel
     * @extends Backbone.View
     */
    var ChannelFormComponentView = Backbone.View.extend({

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

            this.fields = _.extend(this.fields, options.fields);
            this.$channelTypeEl = $(options.channelTypeEl);
            this.$channelTypeEl.on('change', _.bind(this.changeHandler, this));
        },

        /**
         * Open modal window
         *
         * @param e
         */
        changeHandler: function (e) {
            var prevEl = e.removed,
                confirm = new DeleteConfirmation({
                    title:    __('orocrm.channel.confirmation.change_type'),
                    okText:   __('orocrm.channel.confirmation.agree'),
                    content:  __('orocrm.channel.confirmation.submit')
                });

            confirm.on('ok', _.bind(function () {
                this._processChange();
            }, this));

            confirm.on('cancel', _.bind(function () {
                this.$channelTypeEl.select2('val', prevEl.id)
            }, this));

            confirm.open();
        },

        /**
         * Reload form on change form type
         */
        _processChange: function () {
            var $form = $(this.options.formSelector),
                data = $form.serializeArray(),
                url = $form.attr('action'),
                elementNames = _.map(this.options.fields, function(elementIdentifier) {
                    return $(elementIdentifier).attr('name');
                });

            data = _.filter(data, function (field) {
                return _.indexOf(elementNames, field.name) !== -1;
            });
            data.push({name: this.UPDATE_MARKER, value: 1});

            var event = { formEl: $form, data: data, reloadManually: true };
            mediator.trigger('channelViewFormReload:before', event);

            if (event.reloadManually) {
                mediator.execute('submitPage', {url: url, type: $form.attr('method'), data: $.param(data)});
            }
        }
    });

    /**
     * Initialize channel form component
     *
     * @param {Object} options
     */
    return function(options) {
        var view = new ChannelFormComponentView(options);
        options._sourceElement.remove();

        return view;
    }
});
