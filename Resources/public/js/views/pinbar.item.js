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
    },

    unpin: function()
    {
        this.model.destroy({wait: true});
    },

    maximize: function() {
        this.model.set('maximized', new Date().toISOString());
    },

    render: function () {
        if (!_.isUndefined(this.model.attributes.title_rendered)) {
            // to avoid changing title passed by reference
            var data = _.clone(this.model);
            data.attributes = _.clone(this.model.attributes);
            data.attributes.title = data.attributes.title_rendered;
        }
        else {
            var data = this.model;
        }

        this.$el.html(
            this.templates[this.options.type](data.toJSON())
        );
        if (this.model.get('url') ==  window.location.pathname) {
            this.$el.addClass('active');
        }
        return this;
    }
});
