var navigation = navigation || {};

navigation.MainViewAbstract = Backbone.View.extend({
    options: {
        tabTitle: 'Tabs',
        tabIcon: 'icon-folder-close',
        tabId: 'tabs',
        hideTabOnEmpty: false,
        collection: null
    },

    getCollection: function() {
        return this.options.collection;
    },

    registerTab: function() {
        navigation.dotMenu.MainViewInstance.addTab({
            key: this.options.tabId,
            title: this.options.tabTitle,
            icon: this.options.tabIcon,
            hideOnEmpty: this.options.hideTabOnEmpty
        });
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
        var url = '';
        if (OroApp.hashNavigation) {
            url = OroApp.hashNavigation.prototype.getHashUrl();
        } else {
            url = window.location.pathname + window.location.search + window.location.hash;
        }
        return {url: url};
    },

    cleanupTab: function() {
        navigation.dotMenu.MainViewInstance.cleanup(this.options.tabId);
        navigation.dotMenu.MainViewInstance.hideTab(this.options.tabId);
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