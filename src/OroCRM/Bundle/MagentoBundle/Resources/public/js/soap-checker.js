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
        route:           'orocrm_magento_soap_check',
        url:             null,
        id:              null,
        requiredOptions: [
            'websiteSelectEl',
            'websitesListEl',
            'isExtensionInstalledEl',
            'connectorsEl',
            'adminUrlEl',
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

        getUrl: function(type) {
            var params = {id: this.id};
            if (type !== undefined) {
                params.type = type;
            }

            return routing.generate(this.route, params);
        },

        /**
         * Click handler
         */
        processClick: function() {
            var data = this.$el.parents('form').serializeArray();
            var typeData = _.filter(data, function(field) {
                return field.name.indexOf('[type]') !== -1;
            });
            if (typeData.length) {
                typeData = typeData[0].value;
            }

            data = _.filter(data, function(field) {
                return field.name.indexOf('[transport]') !== -1;
            });
            data = _.map(data, function(field) {
                field.name = field.name.replace(/.+\[(.+)\]$/, 'soap-check[$1]');
                return field;
            });
            mediator.execute('showLoading');
            $.post(this.getUrl(typeData), data, _.bind(this.responseHandler, this), 'json')
                .always(_.bind(function(response, status) {
                    mediator.execute('hideLoading');
                    if (status !== 'success') {
                        this.renderResult('error', __('orocrm.magento.error'));
                    }
                }, this));
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
                this.renderErrorMessage();
            }
        },

        /**
         * @param {Object} res
         */
        renderSuccessMessage: function(res) {
            if (res.isExtensionInstalled || false) {
                if (res.isSupportedVersion || false) {
                    this.renderResult('success', __(
                        'orocrm.magento.success_bridge',
                        {extension_version: res.extensionVersion}
                    ));
                } else {
                    this.renderResult(
                        'warning',
                        __(
                            'orocrm.magento.outdated_warning',
                            {
                                extension_version: res.extensionVersion,
                                required_version: res.requiredExtensionVersion
                            }
                        )
                    );
                }
            } else {
                this.renderResult('success', __('orocrm.magento.success'));
            }
        },

        renderErrorMessage: function() {
            this.renderResult('error', __('orocrm.magento.not_valid_parameters'));
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
            $(this.options.adminUrlEl).val(res.adminUrl || '');
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
