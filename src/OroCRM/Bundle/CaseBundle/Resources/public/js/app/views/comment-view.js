define([
    'oronote/js/app/views/note-view',
    'autolinker'
], function(NoteView, autolinker) {
    'use strict';

    var CommentView;

    CommentView = NoteView.extend({
        /**
         * Returns class name for root element
         * @returns {string}
         */
        className: function() {
            return 'list-item' + (this.model.get('public') ? '' : ' private');
        },

        /**
         * @return {Object}
         */
        getTemplateData: function() {
            var data = this.model.toJSON();

            data.collapsed = this.collapsed;
            data.message = autolinker.link(data.message, {className: 'no-hash'});
            data.briefMessage = autolinker.link(data.briefMessage, {className: 'no-hash'});

            return data;
        }
    });

    return CommentView;
});
