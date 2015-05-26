/*global define*/
define([
    'backbone',
], function (Backbone) {
    'use strict';

    /**
     * @class   orocrmmagento.metrics.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            id: null,
            label: null,
            show: true,
            order: 1,
            namePrefix: '',
        }
    });
});
