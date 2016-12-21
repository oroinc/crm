define(function(require) {
    'use strict';

    var MagentoCustomerInfoWidget;
    var mediator = require('oroui/js/mediator');
    var BlockWidget = require('oro/block-widget');

    MagentoCustomerInfoWidget = BlockWidget.extend({

        activeTab: null,

        initializeWidget: function(options) {
            MagentoCustomerInfoWidget.__super__.initializeWidget.call(this, options);
            mediator.trigger('magento-customer-info-widget:init', this, options);
        },

        setActiveTab: function(value) {
            this.activeTab = value;
        },

        prepareContentRequestOptions: function(data, method, url) {
            var options = MagentoCustomerInfoWidget.__super__.prepareContentRequestOptions.call(
                this, data, method, url
            );
            if (this.activeTab) {
                options.data += '&_activeTab=' + this.activeTab;
            }

            return options;
        }
    });
    return MagentoCustomerInfoWidget;
});

