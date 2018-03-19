define(function(require) {
    'use strict';

    var ConfigureIntegrationView;
    var BaseView = require('oroui/js/app/views/base/view');
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');
    var widgetManager = require('oroui/js/widget-manager');

    ConfigureIntegrationView = BaseView.extend({
        optionNames: BaseView.prototype.optionNames.concat(['wid', 'dataFieldSelector', 'apiKeyFieldSelector']),

        /**
         * @inheritDoc
         */
        constructor: function ConfigureIntegrationView() {
            ConfigureIntegrationView.__super__.constructor.apply(this, arguments);
        },

        /**
         * @inheritDoc
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
                var $dataField = this.$(this.dataFieldSelector);
                var $apiKeyField = this.$(this.apiKeyFieldSelector);

                if ($dataField.val() && !$apiKeyField.val()) {
                    var data = JSON.parse($dataField.val());

                    if (data.transport.apiKey) {
                        $apiKeyField.val(data.transport.apiKey);
                    }
                }
            }.bind(this));
        }
    });

    return ConfigureIntegrationView;
});

