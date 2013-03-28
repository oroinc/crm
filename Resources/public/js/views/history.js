var navigation = navigation || {};
navigation.history = navigation.history || {};

navigation.history.MainView = navigation.MainViewAbstract.extend({
    options: {
        tabTitle: 'History',
        tabIcon: 'icon-time',
        collection: null, //navigation.ItemsList
        tabId: 'history'
    },

    initialize: function() {
        this.options.collection = new navigation.ItemsList();

        this.listenTo(this.getCollection(), 'add', this.addItemToTab);
        this.listenTo(this.getCollection(), 'reset', this.addAll);
        this.listenTo(this.getCollection(), 'all', this.render);

        this.$icon = this.$('i');

        this.registerTab();
        this.cleanupTab();
    },

    getCollection: function() {
        return this.options.collection;
    },

    addAll: function(items) {
        items.each(function(item) {
            this.addItemToTab(item);
        }.bind(this));
    },

    render: function() {
        this.checkTabContent();

        return this;
    }
});
