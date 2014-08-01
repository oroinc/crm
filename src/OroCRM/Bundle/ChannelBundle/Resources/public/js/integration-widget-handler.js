define(['underscore', 'backbone', 'orotranslation/js/translator', 'routing', 'oro/dialog-widget'],
    function (_, Backbone, __, routing, DialogWidget) {
        'use strict';

        var $ = Backbone.$;

        /**
         * @export  orocrmchannel/js/integration-widget-handler
         * @class   orocrmchannel.IntegrationWidgetHandlerView
         * @extends Backbone.View
         */
        return Backbone.View.extend({
            /**
             * @type {jQuery|null}
             */
            $valueEl: null,

            /**
             * @type {function(object):string} linkTemplate
             */
            linkTemplate: _.template('<a href="#" class="no-hash" data-role="open-form-widget"><%= title %></a>'),

            /**
             * @type {Object|null}
             * {
             *    id: {number?},
             *    data: {Object.<string, *>?}
             * }
             */
            parsedValue: null,

            /**
             * @type {Object.<string, *>}
             */
            events: {
                'click [data-role="open-form-widget"]': 'openDialog'
            },

            /**
             * @param {{valueEl: string}} options
             */
            initialize: function (options) {
                if (!options.valueEl) {
                    throw new TypeError('"valueEl" is required option for IntegrationWidgetHandlerView');
                }

                this.$valueEl = $(options.valueEl);
            },

            /**
             * @param {jQuery.Event} e
             */
            openDialog: function (e) {
                e.preventDefault();

                var formDialog = new DialogWidget({
                    url: this._getUrl(),
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

                var processFormSave = function (data) {
                    this._setValue('data', data);
                    formDialog.remove();
                };

                formDialog.on('formSave', _.bind(processFormSave, this));
                formDialog.render();
            },

            render: function () {
                this.$el.html(this.linkTemplate({title: this._getTitle()}))
            },

            /**
             * Generates form widget URL based on current state
             *
             * @returns {string}
             * @private
             */
            _getUrl: function () {
                var entityId = this._getValue('id'),
                    data = this._getValue('data'),
                    route = entityId ? 'orocrm_channel_integration_update' : 'orocrm_channel_integration_create',
                    type = this._getValue('type'),
                    params = {};

                if (data) {
                    params.data = data;
                }

                if (entityId) {
                    params.id = entityId;
                } else if (type) {
                    params.type = type;
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
                var entityId = this._getValue('id'),
                    name = this._getValue('integrationName');

                return entityId ? name : __('Create integration');
            },

            /**
             * Get value by key from valueEl
             *
             * @param {string?} key
             * @returns {*}
             * @private
             */
            _getValue: function (key) {
                if (null === this.parsedValue) {
                    var value = this.$valueEl.val();
                    this.parsedValue = value !== '' ? JSON.parse(value) : {};
                }

                return key ? this.parsedValue[key] : this.parsedValue;
            },

            /**
             * Set value to valueEl
             *
             * @param {string}key
             * @param {*} data
             * @private
             */
            _setValue: function (key, data) {
                var currentData = this._getValue();
                currentData[key] = data;

                this.$valueEl.val(JSON.stringify(currentData));
                this.parsedValue = null;
            }
        });
    });
