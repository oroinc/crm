define(function(require) {
    'use strict';

    const NoteView = require('oronote/js/app/views/note-view');
    const autolinker = require('autolinker');

    const CommentView = NoteView.extend({
        /**
         * @inheritdoc
         */
        constructor: function CommentView(options) {
            CommentView.__super__.constructor.call(this, options);
        },

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
            const data = this.model.toJSON();

            data.collapsed = this.collapsed;
            data.message = autolinker.link(data.message, {className: 'no-hash'});
            data.briefMessage = autolinker.link(data.briefMessage, {className: 'no-hash'});

            return data;
        }
    });

    return CommentView;
});
