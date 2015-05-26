/*global define*/
define([
    'backbone',
    './model',
], function (Backbone, Model) {
    'use strict';

    /**
     * @class   orocrmmagento.metrics.Collection
     * @extends Backbone.Collection
     */
    return  Backbone.Collection.extend({
        model: Model
    });
});
