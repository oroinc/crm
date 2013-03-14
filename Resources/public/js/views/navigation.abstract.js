var navigation = navigation || {};

navigation.MainViewAbstract = Backbone.View.extend({
    options: {
        tabTitle: 'Tabs',
        tabIcon: 'icon-folder-close',
        tabId: 'tabs'
    },

    registerTab: function() {
        navigation.dotMenu.MainViewInstance.addTab(this.options.tabId, this.options.tabTitle, this.options.tabIcon, true);
    },

    /**
     * Search for pinbar items for current page.
     *
     * @return {*}
     */
    getItemForCurrentPage: function() {
        return this.options.collection.where(this.getCurrentPageItemData());
    },

    /**
     * Get object with info about current page
     * @return {Object}
     */
    getCurrentPageItemData: function() {
        return {url: window.location.pathname};
    },

    cleanupTab: function() {
        navigation.dotMenu.MainViewInstance.cleanup(this.options.tabId);
    },

    addItemToTab: function(item, prepend) {
        navigation.dotMenu.MainViewInstance.addTabItem(this.options.tabId, item, prepend);
    },

    checkTabContent: function() {
        navigation.dotMenu.MainViewInstance.checkTabContent(this.options.tabId);
    },

    render: function() {
        this.checkTabContent();
        return this;
    }
});