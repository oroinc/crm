/* global define */
define(['backbone', 'routing', 'orocrm/contactphone/model'],
function(Backbone, routing, RegionModel) {
    'use strict';

    /**
     * @export  orocrm/contactphone/collection
     * @class   orocrm.contactphone.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'oro_api_get_contact_phones',
        url: null,
        model: RegionModel,

        /**
         * Constructor
         */
        initialize: function () {
            this.url = routing.generate(this.route);
        },

        /**
         * Regenerate route for selected contact
         *
         * @param id {string}
         */
        setContactId: function (id) {
            this.url = routing.generate(this.route, {contact: id});
        }
    });
});
