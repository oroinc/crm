define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const $ = require('jquery');
    const mediator = require('oroui/js/mediator');
    const widgetManager = require('oroui/js/widget-manager');

    const ConfigureIntegrationView = BaseView.extend({
        optionNames: BaseView.prototype.optionNames.concat(['wid', 'dataFieldSelector', 'apiKeyFieldSelector']),

        /**
         * @inheritdoc
         */
        constructor: function ConfigureIntegrationView(options) {
            ConfigureIntegrationView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            ConfigureIntegrationView.__super__.initialize.call(this, options);

            widgetManager.getWidgetInstance(this.wid, this.onWidgetLoad.bind(this));
        },

        onWidgetLoad: function(widget) {
            mediator.on('integrationFormReload:before', function(event) {
                event.reloadManually = false;
                widget.loadContent($.param(event.data), event.formEl.attr('method'));
            });

            widget.on('contentLoad', function() {
                const $dataField = this.$(this.dataFieldSelector);
                const $apiKeyField = this.$(this.apiKeyFieldSelector);

                if ($dataField.val() && !$apiKeyField.val()) {
                    const data = JSON.parse($dataField.val());

                    if (data.transport.apiKey) {
                        $apiKeyField.val(data.transport.apiKey);
                    }
                }
            }.bind(this));
        }
    });

    return ConfigureIntegrationView;
});

