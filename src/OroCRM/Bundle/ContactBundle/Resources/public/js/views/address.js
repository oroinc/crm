var OroAddressView = Backbone.View.extend({
    template: _.template($("#template-contact-address").html()),

    events: {
        'click .icon-remove': 'close',
        'click .icon-edit': 'edit',
        'click': 'activate'
    },

    initialize: function() {
        this.listenTo(this.model, 'destroy', this.remove)
    },

    activate: function(e) {
        this.trigger('activate', this, this.model);
    },

    edit: function(e) {
        this.trigger('edit', this, this.model);
    },

    close: function()
    {
        this.model.destroy({wait: true});
    },

    render: function () {
        this.$el.html(
            this.template(this.model.toJSON())
        );
        return this;
    }
});
