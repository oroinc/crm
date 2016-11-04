define(function(require) {
    'use strict';

    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var CreateCustomerView = require('orosales/js/app/views/create-customr-view');

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

            mediator.on('sales:customer:select', this.onCustomerSelect, this);

            var $customers = this.$el.find(options.customerSelector);
            _.each($customers, function (customer) {
                this.views.push(new CreateCustomerView({
                    el: customer
                }));
            }, this);
        },

        onCustomerSelect: function(id) {
            var $input = this.$el.find(this.inputSelector);
            $input.inputWidget('val', id, true);
            $input.inputWidget('focus');
        },

        dispose: function() {
            if (this.disposed) {
                return;
            }

            mediator.off('sales:customer:select', this.onCustomerSelect, this);

            CustomerComponent.__super__.dispose.apply(this, arguments);
        }
    });

    return CustomerComponent;
});
