/*global define*/
define(['backbone'],
function (Backbone) {
    'use strict';

    /**
     * @export  orocrmcase/js/comment/model
     * @class   orocrmcase.comment.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            id: null,
            message: null,
            briefMessage: null,
            public: false,
            createdAt: null,
            updatedAt: null,
            permissions: null,
            createdBy: null
        }
    });
});
