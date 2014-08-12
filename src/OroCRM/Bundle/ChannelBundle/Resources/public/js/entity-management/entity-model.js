/*global define*/
/*jslint nomen: true*/
define(['backbone'], function (Backbone) {
    'use strict';

    /**
     * @class   orocrmchannel.entityManagement.EntityModel
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            name : null,
            label: null
        }
    });
});
