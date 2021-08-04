define(['backbone'], function(Backbone) {
    'use strict';

    /**
     * @class   orochannel.entityManagement.Model
     * @extends Backbone.Model
     */
    const EntityManagementModel = Backbone.Model.extend({
        defaults: {
            icon: null,
            name: null,
            label: null,
            actions: []
        },

        /**
         * @inheritdoc
         */
        constructor: function EntityManagementModel(attrs, options) {
            EntityManagementModel.__super__.constructor.call(this, attrs, options);
        }
    });

    return EntityManagementModel;
});
