var OroAddressCollection = Backbone.Collection.extend({
    options: {
        routeParameters: null,
        route: null
    },

    model: OroAddress,

    url: function() {
        return Routing.generate(this.options.route, this.options.routeParameters)
    }
});
