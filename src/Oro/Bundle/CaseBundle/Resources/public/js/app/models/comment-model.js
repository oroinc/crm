import BaseModel from 'oroui/js/app/models/base/model';

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

export default CommentModel;
