define(function(require) {
    'use strict';

    var _ = require('underscore');
    var tools = require('oroui/js/tools');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var CreateCustomerView = require('orosales/js/app/views/create-customer-view');
    var CustomerComponent = BaseComponent.extend({
        views: [],
        $el: null,
        inputSelector: null,
        requiredOptions: [
            'inputSelector',
            'customerSelector'
        ],

        initialize: function(options) {
            _.each(this.requiredOptions, function(optionName) {
                if (!_.has(options, optionName)) {
                    throw new Error('Required option "' + optionName + '" not found.');
                }
            });
            this.inputSelector = options.inputSelector;
            this.$el = options._sourceElement;

            mediator.on('customer-dialog:select', this.onCustomerDialogSelect, this);
            mediator.on('widget_registration:customer-dialog', this.onCustomerDialogInit, this);

            var $customers = this.$el.find(options.customerSelector);
            _.each($customers, function(customer) {
                this.views.push(new CreateCustomerView({
                    el: customer,
                    inputSelector: this.inputSelector
                }));
            }, this);
        },

        onCustomerDialogInit: function(widget) {
            var routeParams = this.$el.find(this.inputSelector).data('select2_query_additional_params') || {};
            widget.options.routeParams = routeParams;

            var widgetUrl =  widget.options.url;
            var widgetUrlRoot = widgetUrl.substring(0, widgetUrl.indexOf('?'));
            var widgetUrlParts = tools.unpackFromQueryString(
                widgetUrl.substring(widgetUrl.indexOf('?'), widgetUrl.length)
            );
            if (!_.isEmpty(routeParams)) {
                routeParams = _.extend({}, widgetUrlParts, {'params': routeParams}, routeParams);
                widgetUrl = widgetUrlRoot || widgetUrl + '?' + tools.packToQueryString(routeParams);
                widget.options.url =  widgetUrl;
            }
        },

        onCustomerDialogSelect: function(id) {
            var $input = this.$el.find(this.inputSelector);
            $input.inputWidget('val', id, true);
            $input.inputWidget('focus');
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('customer-dialog:select', this.onCustomerSelect, this);

            CustomerComponent.__super__.dispose.apply(this, arguments);
        }
    });

    return CustomerComponent;
});
