var navigation = navigation || {};
navigation.favorites = navigation.favorites || {};

navigation.favorites.MainView = navigation.MainViewAbstract.extend({
    options: {
        el: '.favorite-button',
        tabTitle: 'Favorites',
        tabIcon: 'icon-star-empty',
        collection: null,
        tabId: 'favorites'
    },

    events: {
        'click': 'toggleItem'
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

    activate: function() {
        this.$icon.removeClass('icon-white');
    },

    inactivate: function() {
        this.$icon.addClass('icon-white');
    },

    toggleItem: function(e) {
        var current = this.getItemForCurrentPage();
        if (current.length) {
            _.each(current, function(item) {item.destroy({wait: true});});
        } else {
            var el = Backbone.$(e.currentTarget);
            var itemData = this.getCurrentPageItemData()
            if (el.data('url')) {
                itemData['url'] = el.data('url');
            }
            itemData['title'] = el.data('title') ? el.data('title') : document.title;
            itemData['type'] = 'favorite';
            itemData['position'] = this.getCollection().length;
            var currentItem = new navigation.Item(itemData);
            this.getCollection().unshift(currentItem);
            currentItem.save();
        }
    },

    addAll: function(items) {
        items.each(function(item) {
            this.addItemToTab(item);
        }.bind(this));
    },

    render: function() {
        this.checkTabContent();
        if (this.getItemForCurrentPage().length) {
            this.activate();
        } else {
            this.inactivate();
        }
        return this;
    }
});