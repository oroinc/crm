/* global:define */
define(['jquery', 'underscore', 'routing', 'backbone', 'oro/translator', 'oro/navigation', 'oro/messenger'],
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
        route: 'orocrm_magento_soap_check',
        url:   null,

        resultTemplate: _.template(
            '<div class="alert alert-<%= type %> connection-status span5"><%= message %></div>'
        ),

        initialize: function (options) {
            var id = options.transportEntityId || null;
            this.url = routing.generate(this.route, {id: id});

            if (!options.websiteSelectEl || !options.websitesListEl) {
                throw  new TypeError('Missing required options');
            }
        },

        /**
         * Click handler
         *
         * @param e
         */
        processClick: function (e) {
            var data = this.$el.parents('form').serializeArray();
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
            $.post(this.url, data, _.bind(this.responseHandler, this), 'json')
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
                    $websiteSelectEl = $(this.options.websiteSelectEl);

                $listEl.val(JSON.stringify(res.websites));
                $websiteSelectEl.empty();
                _.each(res.websites, function (website) {
                    $websiteSelectEl.append($("<option />").val(website.id).text(website.label));
                });
                $websiteSelectEl.trigger('change');
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
