/* global define */
define(['backbone'],
function(Backbone) {
    'use strict';

    /**
     * @export  orocrm/contactphone/model
     * @class   orocrm.contactphone.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            id: null,
            owner: null,
            phone: null,
            primary: false,
        }
    });
});
