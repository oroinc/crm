var OroAddressBook = Backbone.View.extend({
    template:  _.template($("#template-contact-address-book").html()),

    options: {
        'containerEl': '.map-address-list',
        'mapEl': '.map-visual'
    },

    events: {
        'click': 'toggleItem'
    },

    initialize: function() {
        this.options.collection = new OroAddressCollection();

        this.listenTo(this.getCollection(), 'add', this.addAddress);
        this.listenTo(this.getCollection(), 'reset', this.addAll);
        this.listenTo(this.getCollection(), 'all', this.render);

        this.widget = $(this.template());
        this.$adressesContainer = this.widget.find(this.options.containerEl);
        this.$mapContainer = this.widget.find(this.options.mapEl);
    },

    addAll: function(items) {
        items.each(function(item) {
            this.addAddress(item);
        }, this);
    },

    addAddress: function(address) {
        var addressView = new OroAddressView({
            model: address
        });
        addressView.on('edit', _.bind(this.editAddress, this));
        addressView.on('activate', _.bind(this.activateAddress, this));
        this.$adressesContainer.append(addressView.render().$el);
    },

    editAddress: function(addressView, address) {

    },

    activateAddress: function(addressView, address) {

    },

    render: function() {
        this.$el.append(this.html);
    }
});
