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

    return Backbone.View.extend({
        events: {
            'click': 'processClick'
        },

        /**
         * Check url
         * @property string
         */
        route:           'oro_magento_integration_check',
        url:             null,
        id:              null,
        requiredOptions: [
            'websiteSelectEl',
            'websitesListEl',
            'isExtensionInstalledEl',
            'connectorsEl',
            'extensionVersionEl',
            'magentoVersionEl'
        ],

        resultTemplate: _.template(
            '<div class="alert alert-<%= type %> connection-status"><%= message %></div>'
        ),

        connectorTemplate: _.template(
            '<div class="oro-clearfix">' +
                '<input type="checkbox" id="oro_integration_channel_form_connectors_<%= i %>" ' +
                    'name="oro_integration_channel_form[connectors][]" value="<%= name %>" <%= checked %>>' +
                '<label for="oro_integration_channel_form_connectors_<%= i %>"><%= label %></label>' +
            '</div>'
        ),

        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            this.id = options.transportEntityId || null;
            this.url = this.getUrl();

            var requiredMissed = this.requiredOptions.filter(function(option) {
                return _.isUndefined(options[option]);
            });
            if (requiredMissed.length) {
                throw new TypeError('Missing required option(s): ' + requiredMissed.join(','));
            }
        },

        /**
         * @param additionalParams {Array}
         * @returns {*}
         */
        getUrl: function(additionalParams) {
            var params = _.extend({
                id: this.id
            }, additionalParams || {});

            return routing.generate(this.route, params);
        },

        getIntegrationAndTransportTypeParams: function(fields) {
            var params = {};
            var integrationType = _.first(
                _.filter(fields, function(field) {
                    return field.name.indexOf('[type]') !== -1;
                })
            );

            if (_.isObject(integrationType)) {
                params.type = integrationType.value;
            }

            var transportType = _.first(
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
         *
         * @param fields {Array}
         * @returns {Array}
         */
        getDataForRequestFromFields: function(fields) {
            var data = _.filter(fields, function(field) {
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
            var fields = this.$el.parents('form').serializeArray();
            var transportAndIntegrationTypeParams = this.getIntegrationAndTransportTypeParams(fields);
            var url = this.getUrl(transportAndIntegrationTypeParams);
            var data = this.getDataForRequestFromFields(fields);

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
                this.handleMagentoVersion(res);

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
            this.renderResult('error', res.errorMessage);
        },

        /**
         * websitesModificationAllowed might be undefined, but it should not be false
         * false is equal - denied
         *
         * @param {Object} res
         */
        handleWebsites: function(res) {
            if (this.options.websitesModificationAllowed !== false && res.websites) {
                var $listEl = $(this.options.websitesListEl);
                var $websiteSelectEl = $(this.options.websiteSelectEl);

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
                var connectors = res.connectors;
                var $form = this.$el.parents('form');
                var $connectorsEl = $form.find(this.options.connectorsEl);
                var i = 0;
                var checkedBoxes = $connectorsEl.find(':checked');
                var checked = {};

                _.each(checkedBoxes, function(el) {
                    checked[$(el).val()] = 'checked';
                });

                $connectorsEl.empty();
                for (var key in connectors) {
                    if (connectors.hasOwnProperty(key)) {
                        $connectorsEl.append(
                            this.connectorTemplate({
                                name:    key,
                                label:   connectors[key],
                                checked: checked[key] || '',
                                i:       i
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

        /**
         * Render check result message
         *
         * @param type string
         * @param message string
         */
        renderResult: function(type, message) {
            var container = this.$el.parent();
            container.find('.alert').remove();
            messenger.notificationMessage(type, message, {container: container, template: this.resultTemplate});
        }
    });
});
