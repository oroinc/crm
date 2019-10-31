define([
    'jquery',
    'underscore',
    'routing',
    'backbone',
    'orotranslation/js/translator',
    'oroui/js/mediator',
    'oroui/js/messenger'
], function($, _, routing, Backbone, __, mediator, messenger) {
    'use strict';

    const MadgentoCheckerView = Backbone.View.extend({
        events: {
            click: 'processClick'
        },

        /**
         * Check url
         * @property string
         */
        route: 'oro_magento_integration_check',
        url: null,
        id: null,
        form: null,
        /**
         * Use in case we edit existed integration
         * and element type is disabled
         */
        requiredOptions: [
            'websiteSelectEl',
            'websitesListEl',
            'isExtensionInstalledEl',
            'connectorsEl',
            'extensionVersionEl',
            'magentoVersionEl',
            'sharedGuestEmailListEl',
            'isDisplayOrderNotesEl',
            'isOrderNoteSupportExtensionVersionEl'
        ],

        resultTemplate: _.template(
            '<div class="alert alert-<%= type %> connection-status" role="alert"><%= message %></div>'
        ),

        connectorTemplate: _.template(
            '<div class="oro-clearfix">' +
                '<input type="checkbox" id="oro_integration_channel_form_connectors_<%= i %>" ' +
                    'name="oro_integration_channel_form[connectors][]" value="<%= name %>" <%= checked %>>' +
                '<label for="oro_integration_channel_form_connectors_<%= i %>"><%= label %></label>' +
            '</div>'
        ),

        /**
         * @inheritDoc
         */
        constructor: function MadgentoCheckerView(options) {
            MadgentoCheckerView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.id = options.transportEntityId || null;
            this.url = this.getUrl();

            const requiredMissed = this.requiredOptions.filter(function(option) {
                return _.isUndefined(options[option]);
            });
            if (requiredMissed.length) {
                throw new TypeError('Missing required option(s): ' + requiredMissed.join(','));
            }
        },

        getForm: function() {
            if (this.form !== null && this.form.length) {
                return this.form;
            }

            this.form = this.$el.parents('form');

            if (this.form.length === 0) {
                throw new Error('Expected form not found !');
            }

            return this.form;
        },

        /**
         * @param additionalParams {Array}
         * @returns {*}
         */
        getUrl: function(additionalParams) {
            const params = _.extend({
                id: this.id
            }, additionalParams || {});

            return routing.generate(this.route, params);
        },

        getIntegrationAndTransportTypeParams: function(fields) {
            const params = {};
            const integrationType = _.first(
                _.filter(fields, function(field) {
                    return field.name.indexOf('[type]') !== -1;
                })
            );

            if (_.isObject(integrationType)) {
                params.type = integrationType.value;
            } else {
                /**
                 * In case we on edit page and field type is disabled
                 * so we can't get it from element data array
                 */
                const typeEl = this.getForm().find('[name$="[type]"]').first();
                if (typeEl.length) {
                    params.type = typeEl.val();
                }
            }

            const transportType = _.first(
                _.filter(fields, function(field) {
                    return field.name.indexOf('[transportType]') !== -1;
                })
            );

            if (_.isObject(transportType)) {
                params.transport = transportType.value;
            }

            return params;
        },

        /**
         * @param fields {Array}
         * @returns {Array}
         */
        getDataForRequestFromFields: function(fields) {
            const data = _.filter(fields, function(field) {
                return field.name.indexOf('[transport]') !== -1;
            });

            return _.map(data, function(field) {
                field.name = field.name.replace(/.+\[(.+)\]$/, 'check[$1]');
                return field;
            });
        },

        /**
         * Click handler
         */
        processClick: function() {
            const fields = this.getForm().formToArray();
            const transportAndIntegrationTypeParams = this.getIntegrationAndTransportTypeParams(fields);
            const url = this.getUrl(transportAndIntegrationTypeParams);
            const data = this.getDataForRequestFromFields(fields);

            mediator.execute('showLoading');
            $.post({
                url: url,
                data: data,
                errorHandlerMessage: __('oro.magento.error')
            }).done(_.bind(this.responseHandler, this))
                .always(function() {
                    mediator.execute('hideLoading');
                });
        },

        /**
         * Handler ajax response
         *
         * @param res {object}
         */
        responseHandler: function(res) {
            if (res.success || false) {
                this.handleWebsites(res);
                this.handleIsExtensionInstalled(res);
                this.handleAdminUrl(res);
                this.handleConnectors(res);
                this.handleExtensionVersion(res);
                this.handleIsOrderNoteSupportExtensionVersion(res);
                this.handleMagentoVersion(res);
                this.handleSharedGuestEmailListEl(res);

                this.renderSuccessMessage(res);
            } else {
                this.renderErrorMessage(res);
            }
        },

        /**
         * @param {Object} res
         */
        renderSuccessMessage: function(res) {
            if (res.isExtensionInstalled || false) {
                if (res.isSupportedVersion || false) {
                    this.renderResult('success', __(
                        'oro.magento.success_bridge',
                        {extension_version: res.extensionVersion}
                    ));
                } else {
                    this.renderResult(
                        'warning',
                        __(
                            'oro.magento.outdated_warning',
                            {
                                extension_version: res.extensionVersion,
                                required_version: res.requiredExtensionVersion
                            }
                        )
                    );
                }
            } else {
                this.renderResult('success', __('oro.magento.success'));
            }
        },

        renderErrorMessage: function(res) {
            if (res.errorMessage) {
                this.renderResult('error', res.errorMessage);
            } else {
                this.renderResult('error', __('oro.magento.error'));
            }
        },

        /**
         * websitesModificationAllowed might be undefined, but it should not be false
         * false is equal - denied
         *
         * @param {Object} res
         */
        handleWebsites: function(res) {
            if (this.options.websitesModificationAllowed !== false && res.websites) {
                const $listEl = $(this.options.websitesListEl);
                const $websiteSelectEl = $(this.options.websiteSelectEl);

                $listEl.val(JSON.stringify(res.websites));
                $websiteSelectEl.empty();
                _.each(res.websites, function(website) {
                    $websiteSelectEl.append($('<option />').val(website.id).text(website.label));
                });
                $websiteSelectEl.trigger('change');
            }
        },

        /**
         * @param {Object} res
         */
        handleIsExtensionInstalled: function(res) {
            $(this.options.isExtensionInstalledEl)
                .val(res.isExtensionInstalled || false ? 1 : 0);
        },

        /**
         * @param {Object} res
         */
        handleIsOrderNoteSupportExtensionVersion: function(res) {
            const isOrderNoteSupportDisabledAttrValue = (res.isOrderNoteSupportExtensionVersion || false) === false;
            $(this.options.isDisplayOrderNotesEl).inputWidget('disable', isOrderNoteSupportDisabledAttrValue);

            $(this.options.isOrderNoteSupportExtensionVersionEl)
                .val(res.isOrderNoteSupportExtensionVersion || false ? 1 : 0);
        },

        /**
         * @param {Object} res
         */
        handleAdminUrl: function(res) {
            if (this.options.adminUrlEl) {
                $(this.options.adminUrlEl).val(res.adminUrl || '');
            }
        },

        /**
         * @param {Object} res
         */
        handleConnectors: function(res) {
            if (res.connectors) {
                const connectors = res.connectors;
                const $form = this.$el.parents('form');
                const $connectorsEl = $form.find(this.options.connectorsEl);
                let i = 0;
                const checkedBoxes = $connectorsEl.find(':checked');
                const checked = {};

                _.each(checkedBoxes, function(el) {
                    checked[$(el).val()] = 'checked';
                });

                $connectorsEl.empty();
                for (const key in connectors) {
                    if (connectors.hasOwnProperty(key)) {
                        $connectorsEl.append(
                            this.connectorTemplate({
                                name: key,
                                label: connectors[key],
                                checked: checked[key] || '',
                                i: i
                            })
                        );
                        i++;
                    }
                }
            }
        },

        handleExtensionVersion: function(res) {
            $(this.options.extensionVersionEl).val(res.extensionVersion || '');
        },

        handleMagentoVersion: function(res) {
            $(this.options.magentoVersionEl).val(res.magentoVersion || '');
        },

        handleSharedGuestEmailListEl: function(res) {
            const disabledAttrValue = (res.isExtensionInstalled || false) === false;
            $(this.options.sharedGuestEmailListEl).prop(
                'disabled',
                disabledAttrValue
            );
        },

        /**
         * Render check result message
         *
         * @param type string
         * @param message string
         */
        renderResult: function(type, message) {
            const container = this.$el.parent();
            container.find('.alert').remove();
            messenger.notificationMessage(type, message, {container: container, template: this.resultTemplate});
        }
    });

    return MadgentoCheckerView;
});
