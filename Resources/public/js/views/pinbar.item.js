var navigation = navigation || {};
navigation.pinbar = navigation.pinbar || {};

navigation.pinbar.ItemView = Backbone.View.extend({

    options: {
        type: 'list'
    },

    tagName:  'li',

    templates: {
        list: _.template($("#template-list-pin-item").html()),
        tab: _.template($("#template-tab-pin-item").html())
    },

    events: {
        'click .btn-close': 'unpin',
        'click .close': 'unpin',
        'click .pin-holder div a': 'maximize',
        'click span': 'maximize'
    },

    initialize: function() {
        this.listenTo(this.model, 'destroy', this.remove)
        this.listenTo(this.model, 'change:display_type', this.remove);
        /**
         * Change active pinbar item after hash navigation request is completed
         */
        OroApp.Events.bind(
            "hash_navigation_request:complete",
            function() {
                this.setActiveItem();
            },
            this
        );
    },

    unpin: function()
    {
        this.model.destroy({wait: true});
    },

    maximize: function() {
        this.model.set('maximized', new Date().toISOString());
    },

    setActiveItem: function() {
        if (this.model.get('url') ==  OroApp.hashNavigation.prototype.getHashUrl()) {
            this.$el.addClass('active');
        } else {
            this.$el.removeClass('active');
        }
    },

    render: function () {
        this.$el.html(
            this.templates[this.options.type](this.model.toJSON())
        );
        this.setActiveItem();
        return this;
    }
});
