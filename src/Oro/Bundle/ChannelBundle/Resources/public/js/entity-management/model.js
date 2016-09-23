define(['backbone'], function(Backbone) {
    'use strict';

    /**
     * @class   orochannel.entityManagement.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            icon: null,
            name: null,
            label: null,
            actions: []
        }
    });
});
