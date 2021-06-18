define([
    'oroui/js/app/models/base/model'
], function(BaseModel) {
    'use strict';

    const CommentModel = BaseModel.extend({
        defaults: {
            'id': null,
            'message': null,
            'briefMessage': null,
            'public': false,
            'createdAt': null,
            'updatedAt': null,
            'permissions': null,
            'createdBy': null,
            'updatedBy': null
        },

        /**
         * @inheritdoc
         */
        constructor: function CommentModel(...args) {
            CommentModel.__super__.constructor.apply(this, args);
        }
    });

    return CommentModel;
});
