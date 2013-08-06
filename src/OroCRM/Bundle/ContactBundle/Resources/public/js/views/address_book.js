var OroAddressBook = Backbone.View.extend({
    options: {
        'containerEl': '.map-address-list',
        'mapEl': '.map-visual',
        'mapZoom': 17,
        'mapType': null
    },

    initialize: function() {
        function initialize() {
            var mapOptions = {
                zoom: 8,
                center: new google.maps.LatLng(-34.397, 150.644),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            var map = new google.maps.Map(document.getElementById('map-canvas'),
                mapOptions);
        }

        this.options.collection = this.options.collection || new OroAddressCollection();

        this.listenTo(this.getCollection(), 'add', this.addAddress);
        this.listenTo(this.getCollection(), 'reset', this.addAll);
        this.listenTo(this.getCollection(), 'all', this.render);

        this.$adressesContainer = this.$el.find(this.options.containerEl);
        this.$mapContainer = this.$el.find(this.options.mapEl);
    },

    getCollection: function() {
        return this.options.collection;
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

    editAddress: function() {
        Oro.widget.Manager.createWidget('dialog', {
            'el': '<div/>',
            'title': 'Edit Address',
            'dialogOptions': {
                'modal': true
            }
        }).render();
    },

    activateAddress: function(addressView, address) {
        this.$adressesContainer.find('.active').removeClass('active');
        addressView.markActive();
        this.updateMap(address);
    },

    updateMap: function(address) {
        this.getGeocoder().geocode({'address': this.getAddressString(address)}, _.bind(function(results, status) {
            if(status == google.maps.GeocoderStatus.OK) {
                //Move location marker and map center to new coordinates
                this.updateMapLocation(results[0].geometry.location, address);
            }
        }, this));
    },

    getAddressString: function(address) {
        return address.get('country') + ', '
            + address.get('city') + ', '
            + address.get('street') + ' ' + address.get('street2');
    },

    updateMapLocation: function(location, address) {
        this._initMap(location);
        this.map.setCenter(location);
        this.mapLocationMarker.setPosition(location);
        this.mapLocationMarker.setTitle(address.get('label'))
    },

    getGeocoder: function() {
        if (_.isUndefined(this.geocoder)) {
            this.geocoder = new google.maps.Geocoder();
        }
        return this.geocoder;
    },

    _initMap: function(location) {
        this.$mapContainer.css({
            'width': '100%',
            'height': '400px'
        });
        this.$mapContainer.show();
        var mapOptions = {
            zoom: this.options.mapZoom,
            mapTypeId: this.options.mapType || google.maps.MapTypeId.ROADMAP,
            center: location
        };
        this.map = new google.maps.Map(this.$mapContainer[0], mapOptions);

        this.mapLocationMarker = new google.maps.Marker({
            draggable: false,
            map: this.map,
            position: location
        });
    }
});
