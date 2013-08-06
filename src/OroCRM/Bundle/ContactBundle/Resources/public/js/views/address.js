var OroAddressView = Backbone.View.extend({
    tagName: 'div',

    attributes: {
        'class': 'map-item'
    },

    events: {
        'click': 'activate',
        'click button:has(.icon-remove)': 'close',
        'click button:has(.icon-edit)': 'edit'
    },

    initialize: function() {
        this.template = _.template($("#template-contact-address").html());
        this.listenTo(this.model, 'destroy', this.remove)
    },

    markActive: function() {
        this.$el.addClass('active');
    },

    activate: function() {
        this.trigger('activate', this, this.model)
    },

    edit: function(e) {
        this.trigger('edit', this, this.model);
    },

    close: function()
    {
        if (this.model.get('isPrimary')) {
            alert(_.__('Primary address can not be removed'));
        } else {
            // TODO: destroy model on delete, remove will be called on destroy event
            //this.model.destroy();
            this.remove();
        }
    },

    render: function () {
        this.$el.append(
            this.template(this.model.toJSON())
        );
        if (this.model.get('isPrimary')) {
            this.activate();
        }
        return this;
    }
});
