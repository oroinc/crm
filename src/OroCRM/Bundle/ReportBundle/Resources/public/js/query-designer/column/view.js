/* global define */
define(['underscore', 'backbone'],
function(_, Backbone) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/query-designer/column/view
     * @class   oro.queryDesigner.column.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Object} */
        options: {
            templateSelector: null
        },

        initialize: function() {
            var templateHtml = $(this.options.templateSelector).html();
            this.template = _.template(templateHtml);

            this.listenTo(this.model, 'destroy', this.remove);
        },

        render: function() {
            this.$el.html(this.template(this.model.toJSON()));

            return this;
        }
    });
});
