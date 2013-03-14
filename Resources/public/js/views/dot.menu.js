var navigation = navigation || {};
navigation.dotMenu = navigation.dotMenu || {};

navigation.dotMenu.MainView = Backbone.View.extend({
    options: {
        el: '.pin-menus .tabbable'
    },
    tabs: {},

    templates: {
        tab: _.template($("#template-dot-menu-tab").html()),
        content: _.template($("#template-dot-menu-tab-content").html())
    },

    initialize: function() {
        this.$tabsContainer = this.$('.nav-tabs');
        this.$tabsContent = this.$('.tab-content')
    },

    addTab: function(key, title, icon, hideOnEmpty) {
        hideOnEmpty = _.isUndefined(hideOnEmpty) ? false : hideOnEmpty;
        var data = {key: key, title: title, icon: icon, hideOnEmpty: hideOnEmpty};

        data.$tab = this.$('#' + key + '-tab');
        if (!data.$tab.length) {
            data.$tab = $(this.templates.tab(data));
            this.$tabsContainer.append(data.$tab);
        }

        data.$tabContent = this.$('#' + key + '-content');
        if (!data.$tabContent.length) {
            data.$tabContent = $(this.templates.content(data));
            this.$tabsContent.append(data.$tabContent);
        }

        data.$tabContentContainer = data.$tabContent.find('ul');
        this.tabs[key] = data;
    },

    getTab: function(key) {
        return this.tabs[key];
    },

    addTabItem: function(tabKey, item, prepend) {
        var el = null;
        if (_.isElement(item)) {
            el = item;
        } else if (_.isObject(item)) {
            if (!_.isFunction(item.render)) {
                item = new navigation.dotMenu.ItemView({model: item});
            }
            el = item.render().$el;
        }

        if (el) {
            if (prepend) {
                this.getTab(tabKey).$tabContentContainer.prepend(el);
            } else {
                this.getTab(tabKey).$tabContentContainer.append(el);
            }
        }
    },

    cleanup: function(tabKey) {
        var tab = this.getTab(tabKey);
        tab.$tabContentContainer.empty();
        if (tab.hideOnEmpty) {
            tab.$tab.hide();
        }
    },

    checkTabContent: function(tabKey) {
        var tab = this.getTab(tabKey);
        if (tab.$tabContentContainer.children().length) {
            tab.$tab.show();
        } else {
            tab.$tab.hide();
        }
    }
});

$(function() {
    window.navigation.dotMenu.MainViewInstance = new navigation.dotMenu.MainView();
});

