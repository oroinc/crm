/*global define, alert*/
define(['underscore', 'oronote/js/note/view', 'autolinker'],
function (_, NoteView, autolinker) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  orocrmcase/js/comment/view
     * @class   orocrmcase.comment.View
     * @extends oronote.note.View
     */
    return NoteView.extend({
        /**
         * @return {orocrmcase.comment.View}
         */
        render: function () {
            if (!this.model.get('public')) {
                this.$el.addClass('private');
            } else {
                this.$el.removeClass('private');
            }

            return NoteView.prototype.render.apply(this, arguments);
        },

        /**
         * @return {Object}
         */
        _prepareTemplateData: function () {
            var data = this.model.toJSON();

            data.collapsed = this.collapsed;
            data.message = autolinker.link(data['message'], {className: 'no-hash'});
            data.briefMessage = autolinker.link(data['briefMessage'], {className: 'no-hash'});

            return data;
        }
    });
});
