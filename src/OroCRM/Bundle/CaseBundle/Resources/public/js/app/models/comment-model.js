/*global define*/
define([
    'oroui/js/app/models/base/model'
], function (BaseModel) {
    'use strict';

    var NoteModel;

    NoteModel = BaseModel.extend({
        defaults: {
            id: null,
            message: null,
            briefMessage: null,
            'public': false,
            createdAt: null,
            updatedAt: null,
            permissions: null,
            createdBy: null,
            updatedBy: null
        }
    });

    return NoteModel;
});
