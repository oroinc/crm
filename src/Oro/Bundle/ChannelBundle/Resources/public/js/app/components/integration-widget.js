define([
    'underscore',
    'backbone',
    'orotranslation/js/translator',
    'routing',
    'oro/dialog-widget',
    'oroui/js/delete-confirmation',
    'text-loader!./template/integration-widget-link.html'
],
function(_, Backbone, __, routing, DialogWidget, DeleteConfirmation, linkTemplate) {
    'use strict';

    const $ = Backbone.$;

    /**
     * @class   orochannel.app.components.IntegrationWidgetView
     * @extends Backbone.View
     */
    const IntegrationWidgetView = Backbone.View.extend({
        /**
         * @type {jQuery}
         */
        $dataEl: null,

        /**
         * @type {jQuery}
         */
        $idEl: null,

        /**
         * @type {jQuery}
         */
        $typeEl: null,

        /**
         * @type {jQuery}
         */
        $nameEl: null,

        /**
         * @type {jQuery}
         */
        $channelNameEl: null,

        /**
         * @type {function(object):string} linkTemplate
         */
        linkTemplate: _.template(linkTemplate),

        /**
         * @type {Object.<string, *>}
         */
        events: {
            'click [data-purpose="open-form-widget"]': 'openDialog',
            'click [data-purpose="remove-integration-data"]': 'onRemoveIntegrationData'
        },

        /**
         * @inheritdoc
         */
        constructor: function IntegrationWidgetView(options) {
            IntegrationWidgetView.__super__.constructor.call(this, options);
        },

        /**
         * Initialize.
         *
         * @param {Object} options
         */
        initialize: function(options) {
            if (!(options.dataEl && options.idEl && options.typeEl && options.nameEl)) {
                throw new TypeError('Missing required options for IntegrationWidgetHandlerView');
            }

            this.$dataEl = $(options.dataEl);
            this.$idEl = $(options.idEl);
            this.$typeEl = $(options.typeEl);
            this.$nameEl = $(options.nameEl);
            this.$channelNameEl = $(options.channelNameEl);
        },

        /**
         * @param {jQuery.Event} e
         */
        openDialog: function(e) {
            e.preventDefault();

            let url = this._getUrl();
            const routeAdditionalParams = $(e.target).data('route_additional_params');
            if (routeAdditionalParams) {
                url = url + (url.indexOf('?') === -1 ? '?' : '&') + $.param(routeAdditionalParams);
            }

            const formDialog = new DialogWidget({
                url: url,
                title: this._getTitle(),
                stateEnabled: false,
                incrementalPosition: false,
                dialogOptions: {
                    modal: true,
                    resizable: true,
                    autoResize: true,
                    width: 700,
                    height: 550
                }
            });

            const processFormSave = function(data) {
                data = _.omit(data, ['_token']);

                this._setValue('name', data.name || '');
                this._setValue('data', data);
                formDialog.remove();
                this.render();
            };

            formDialog.on('formSave', processFormSave.bind(this));
            formDialog.render();
        },

        onRemoveIntegrationData: function() {
            const confirm = new DeleteConfirmation({
                content: __('oro.channel.confirmation.delete_confirmation')
            });
            confirm.on('ok', this.removeIntegrationData.bind(this));
            confirm.open();
        },

        /**
         * Clears storage elements and re-render widget link
         */
        removeIntegrationData: function() {
            this.$dataEl.val(null);
            this.$idEl.val(null);
            this.$nameEl.val(null);

            this.render();
        },

        /**
         * @inheritdoc
         */
        render: function() {
            const name = this._getValue('name');
            const templateContext = {
                name: name,
                title: name ? __('edit') : __('Configure integration')
            };

            this.$el
                .find('.integration-widget-link-container')
                .remove()
                .end()
                .append(this.linkTemplate(templateContext));
        },

        /**
         * Generates form widget URL based on current state
         *
         * @returns {string}
         * @private
         */
        _getUrl: function() {
            const entityId = this._getValue('id');
            const data = this._getValue('data');
            const route = entityId ? 'oro_channel_integration_update' : 'oro_channel_integration_create';
            const type = this._getValue('type');
            const params = {};

            params.channelName = encodeURIComponent(this._getValue('channelName'));

            if (data) {
                params.data = data;
            }

            if (entityId) {
                params.id = entityId;
            } else if (type) {
                params.type = encodeURIComponent(type);
            }

            return routing.generate(route, params);
        },

        /**
         * Returns title for window
         *
         * @returns {string}
         * @private
         */
        _getTitle: function() {
            const name = this._getValue('name');

            return name ? name : __('Configure integration');
        },

        /**
         * Get value by key
         *
         * @param {string?} key
         * @returns {*}
         * @private
         */
        _getValue: function(key) {
            this._assertAllowedValueKey(key);

            let preparedData;
            const data = this[['$', key, 'El'].join('')].val();

            switch (key) {
                case 'data':
                    preparedData = data !== '' ? JSON.parse(data) : {};
                    break;
                default:
                    preparedData = data;
            }

            return preparedData;
        },

        /**
         * Set value by key
         *
         * @param {string}key
         * @param {*} data
         * @private
         */
        _setValue: function(key, data) {
            let preparedData;

            this._assertAllowedValueKey(key);
            switch (key) {
                case 'data':
                    preparedData = JSON.stringify(data);
                    break;
                default:
                    preparedData = data;
            }

            this[['$', key, 'El'].join('')].val(preparedData);
        },

        /**
         * Checks whether data key is supported
         *
         * @param {string}key
         * @private
         */
        _assertAllowedValueKey: function(key) {
            if (['id', 'data', 'type', 'name', 'channelName'].indexOf(key) === -1) {
                throw new TypeError('Unknown option: ' + key);
            }
        }
    });

    /**
     * Initialize widget component
     *
     * @param {Object} options
     */
    return function(options) {
        const view = new IntegrationWidgetView(options);
        view.render();

        options._sourceElement.remove();

        return view;
    };
});
