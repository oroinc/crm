import mediator from 'oroui/js/mediator';
import BlockWidget from 'oro/block-widget';

const CustomerInfoWidget = BlockWidget.extend({

    activeTab: null,

    initializeWidget: function(options) {
        CustomerInfoWidget.__super__.initializeWidget.call(this, options);
        mediator.trigger('customer-info-widget:init', this, options);
    },

    setActiveTab: function(value) {
        this.activeTab = value;
    },

    prepareContentRequestOptions: function(data, method, url) {
        const options = CustomerInfoWidget.__super__.prepareContentRequestOptions.call(
            this, data, method, url
        );
        if (this.activeTab) {
            options.data += '&_activeTab=' + this.activeTab;
        }

        return options;
    }
});
export default CustomerInfoWidget;

