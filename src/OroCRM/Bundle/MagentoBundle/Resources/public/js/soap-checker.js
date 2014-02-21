/* global:define */
define(['jquery', 'underscore', 'routing', 'backbone', 'oro/translator', 'oronavigation/js/navigation', 'oro/messenger'],
    function ($, _, routing, Backbone, __, Navigation, messenger) {
        "use strict";

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
        requiredOptions: ['websiteSelectEl', 'websitesListEl', 'isExtensionInstalledEl', 'connectorsEl'],

        resultTemplate: _.template(
            '<div class="alert alert-<%= type %> connection-status"><%= message %></div>'
        ),

        connectorTemplate: _.template(
            '<div class="oro-clearfix">' +
                '<input type="checkbox" id="oro_integration_channel_form_connectors_<%= i %>" ' +
                    'name="oro_integration_channel_form[connectors][]" value="<%= name %>">' +
                '<label for="oro_integration_channel_form_connectors_<%= i %>"><%= label %></label>' +
            '</div>'
        ),

        initialize: function (options) {
            this.id = options.transportEntityId || null;
            this.url = this.getUrl();

            var requiredMissed = this.requiredOptions.filter(function (option) {
                return _.isUndefined(options[option]);
            });
            if (requiredMissed.length) {
                throw new TypeError('Missing required option(s): ' + requiredMissed.join(','));
            }
        },

        getUrl: function (type) {
            var params = {id: this.id};
            if (type !== undefined) {
                params.type = type;
            }

            return routing.generate(this.route, params);
        },

        /**
         * Click handler
         *
         * @param e
         */
        processClick: function (e) {
            var data = this.$el.parents('form').serializeArray();
            var typeData = _.filter(data, function (field) {
                return field.name.indexOf('[type]') !== -1;
            });
            if (typeData.length) {
                typeData = typeData[0].value;
            }

            data = _.filter(data, function (field) {
                return field.name.indexOf('[transport]') !== -1;
            });
            data = _.map(data, function (field) {
                field.name = field.name.replace(/.+\[(.+)\]$/, 'soap-check[$1]')
                return field;
            });
            var navigation = Navigation.getInstance();
            if (navigation) {
                navigation.loadingMask.show();
            }
            $.post(this.getUrl(typeData), data, _.bind(this.responseHandler, this), 'json')
                .always(_.bind(function (respose, status) {
                    if (navigation) {
                        navigation.loadingMask.hide();
                    }
                    if (status !== 'success') {
                        this.renderResult('error', __('Error occurred during check request, please try later!'));
                    }
                }, this));
        },

        /**
         * Handler ajax response
         *
         * @param res {}
         */
        responseHandler: function (res) {
            var success = res.success || false,
                message = success ? __('Connection succeed, please choose website.') : __('Parameters are not valid!');

            if (success && res.websites) {
                var $listEl = $(this.options.websitesListEl),
                    $websiteSelectEl = $(this.options.websiteSelectEl),
                    $isExtensionInstalledEl = $(this.options.isExtensionInstalledEl);

                $listEl.val(JSON.stringify(res.websites));
                $websiteSelectEl.empty();
                _.each(res.websites, function (website) {
                    $websiteSelectEl.append($("<option />").val(website.id).text(website.label));
                });
                $websiteSelectEl.trigger('change');
                $isExtensionInstalledEl.val(res.isExtensionInstalled || false ? 1 : 0);
            }

            if (success && res.connectors) {
                var connectors = res.connectors;
                var form = this.$el.parents('form');
                form.find(this.options.connectorsEl).empty();
                var i = 0;
                for (var key in connectors) {
                    form.find(this.options.connectorsEl).append(this.connectorTemplate({name: key, label: connectors[key], i: i}));
                    i++;
                }
            }

            this.renderResult(success ? 'success' : 'error', message);
        },

        /**
         * Render check result message
         *
         * @param type string
         * @param message string
         */
        renderResult: function (type, message) {
            messenger.notificationFlashMessage(type, message, {container: this.$el.parent(), template: this.resultTemplate});
        }
    });
});
