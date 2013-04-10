var navigation = navigation || {};
navigation.favorites = navigation.favorites || {};

navigation.favorites.MainView = navigation.MainViewAbstract.extend({
    options: {
        el: '.favorite-button',
        tabTitle: 'Favorites',
        tabIcon: 'icon-star-empty',
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

            itemData['title'] = document.title;
            itemData['type'] = 'favorite';
            itemData['position'] = this.getCollection().length;
            var currentItem = new navigation.Item(itemData);

            this.getCollection().unshift(currentItem);

            currentItem.attributes.title = _.isObject(el.data('title')) ? JSON.stringify(el.data('title')) : '{"template": "' + itemData['title'] + '"}';

            currentItem.save();
        }
    },

    addAll: function(items) {
        items.each(function(item) {
            this.addItemToTab(item);
        }, this);
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
