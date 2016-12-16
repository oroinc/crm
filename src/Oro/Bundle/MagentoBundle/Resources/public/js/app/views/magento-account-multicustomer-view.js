define(function(require) {
    'use strict';

    var MagentoAccountMulticustomerView;
    var _ = require('underscore');
    var $ = require('jquery');
    var BaseView = require('oroui/js/app/views/base/view');

    MagentoAccountMulticustomerView = BaseView.extend({

        activeTab: null,

        events: {
            'shown.bs.tab .tab-content': 'onTabShown',
            'shown.bs.tab >.oro-tabs>ul': 'onCustomerShown',
        },

        listen: {
            'magento-customer-info-widget:init mediator': function(widget, options) {
                if (this.$(options.container || options.el).length !== 0) {
                    widget.setActiveTab(this.activeTab);
                }
            }
        },

        onTabShown: function(e) {
            var contentId = this.$(e.target).data('target');
            var targetRegExp = this.makeTargetRegExp('(.*)');
            this.activeTab = _.result(contentId.match(targetRegExp), 1, null);
        },

        onCustomerShown: function(e) {
            if (this.activeTab) {
                var targetRegExp = this.makeTargetRegExp(this.activeTab);
                var $link = this.$('a[data-target]:visible').filter(function() {
                    return targetRegExp.test($(this).data('target'));
                });
                if ($link.length === 1 && !$link.parent().hasClass('active')) {
                    $link.click();
                }
            }
        },

        makeTargetRegExp: function(activeTabPlaceholder) {
            return new RegExp('#oro_magento_customer_' + activeTabPlaceholder + '_customer_\\d+_channel_\\d+');
        }
    });

    return MagentoAccountMulticustomerView;
});
