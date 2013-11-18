/* global define */
define(['backbone', 'routing', 'orocrm/contactphone/model'],
function(Backbone, routing, ContactPhoneModel) {
    'use strict';

    /**
     * @export  orocrm/contactphone/collection
     * @class   orocrm.contactphone.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'oro_api_get_contact_phones',
        url: null,
        model: ContactPhoneModel,

        /**
         * Constructor
         */
        initialize: function () {
        },

        /**
         * Regenerate route for selected contact
         *
         * @param id {string}
         */
        setContactId: function (contactId) {
            this.url = routing.generate(this.route, {contactId: contactId});
        }
    });
});
