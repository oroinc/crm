var OroAddressBook = Backbone.View.extend({
    options: {
        'mapZoom': 17,
        'mapType': null,
        'template': null,
        'entityId': null
    },

    mapLocationCache: {},

    attributes: {
        'class': 'map-box'
    },

    initialize: function() {
        this.options.collection = this.options.collection || new OroAddressCollection(null, {
            'route': 'oro_api_get_contact_addresses',
            'routeParameters': {'contactId': this.options.entityId}
        });

        this.listenTo(this.getCollection(), 'add', this.addAddress);
        this.listenTo(this.getCollection(), 'reset', this.addAll);
        //this.listenTo(this.getCollection(), 'all', this.render);

        this.$adressesContainer = Backbone.$('<div class="map-address-list"/>').appendTo(this.$el);
        this.$mapContainer = Backbone.$('<div class="map-visual"/>').appendTo(this.$el);
    },

    getCollection: function() {
        return this.options.collection;
    },

    addAll: function(items) {
        this.$adressesContainer.empty();
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
        this._openAddressEditForm(
            _.__('Update Address'),
            Routing.generate(
                'orocrm_contact_address_update',
                {
                    'contactId': this.options.entityId,
                    'id': address.get('id')
                }
            )
        );
    },

    createAddress: function() {
        this._openAddressEditForm(
            _.__('Create Address'),
            Routing.generate(
                'orocrm_contact_address_create',
                {
                    'contactId': this.options.entityId
                }
            )
        );
    },

    _openAddressEditForm: function(title, url) {
        var addressEditDialog = Oro.widget.Manager.createWidget('dialog', {
            'url': url,
            'title': title,
            'stateEnabled': false,
            'dialogOptions': {
                'modal': false,
                'resizable': false,
                'width': 400,
                'height': 420
            }
        });
        addressEditDialog.render();
        addressEditDialog.on('formSave', _.bind(function() {
            addressEditDialog.close();
            Oro.NotificationFlashMessage('success', _.__('Address successfully saved'));
            this.reloadAddresses();
        }, this));
    },

    reloadAddresses: function() {
        this.getCollection().fetch({reset: true});
    },

    activateAddress: function(addressView, address) {
        this.$adressesContainer.find('.active').removeClass('active');
        addressView.markActive();
        this.updateMap(address);
    },

    updateMap: function(address) {
        var addressString = this.getAddressString(address);
        if (this.mapLocationCache.hasOwnProperty(addressString)) {
            this.updateMapLocation(this.mapLocationCache[addressString], address);
        } else {
            this.getGeocoder().geocode({'address': addressString}, _.bind(function(results, status) {
                if(status == google.maps.GeocoderStatus.OK) {
                    //Move location marker and map center to new coordinates
                    this.updateMapLocation(results[0].geometry.location, address);
                }
            }, this));
        }
    },

    getAddressString: function(address) {
        return address.get('country') + ', '
            + address.get('city') + ', '
            + address.get('street') + ' ' + address.get('street2');
    },

    updateMapLocation: function(location, address) {
        if (location && (!this.location || location.toString() != this.location.toString())) {
            this._initMap(location);
            this.map.setCenter(location);
            this.mapLocationMarker.setPosition(location);
            this.mapLocationMarker.setTitle(address.get('label'));
            this.location = location;
        }
    },

    getGeocoder: function() {
        if (_.isUndefined(this.geocoder)) {
            this.geocoder = new google.maps.Geocoder();
        }
        return this.geocoder;
    },

    _initMap: function(location) {
        this.$mapContainer.show();
        var mapOptions = {
            zoom: this.options.mapZoom,
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
            },
            panControl: false,
            zoomControl: true,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle.SMALL
            },
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
