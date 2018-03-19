define(['backbone'], function(Backbone) {
    'use strict';

    var EntityManagementModel;
    /**
     * @class   orochannel.entityManagement.Model
     * @extends Backbone.Model
     */
    EntityManagementModel = Backbone.Model.extend({
        defaults: {
            icon: null,
            name: null,
            label: null,
            actions: []
        },

        /**
         * @inheritDoc
         */
        constructor: function EntityManagementModel() {
            EntityManagementModel.__super__.constructor.apply(this, arguments);
        }
    });

    return EntityManagementModel;
});
