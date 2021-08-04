define(function(require) {
    'use strict';

    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const DialogWidget = require('oro/dialog-widget');
    const BaseView = require('oroui/js/app/views/base/view');

    const CustomerView = BaseView.extend({
        events: {
            'click button': 'onCreate'
        },

        dialogWidget: null,

        /**
         * @inheritdoc
         */
        constructor: function CustomerView(options) {
            CustomerView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            CustomerView.__super__.initialize.call(this, options);

            this.options = _.defaults(options || {}, this.options);
        },

        onCreate: function() {
            const customer = this.$('[data-customer]').data('customer');
            const routeParams = this.$el.parents()
                .find(this.options.inputSelector)
                .data('select2_query_additional_params') || {};

            this.dialogWidget = new DialogWidget({
                title: __('Create {{ entity }}', {entity: this.$el.text()}),
                url: routing.generate(customer.routeCreate, routeParams),
                stateEnabled: false,
                incrementalPosition: true,
                dialogOptions: {
                    modal: true,
                    allowMaximize: true,
                    width: 1280,
                    height: 650
                }
            });

            this.dialogWidget.once('formSave', id => {
                this.dialogWidget.remove();
                this.dialogWidget = null;

                mediator.trigger(
                    'customer-dialog:select',
                    JSON.stringify({entityClass: customer.className, entityId: id})
                );
            });

            this.dialogWidget.render();
        }
    });

    return CustomerView;
});
