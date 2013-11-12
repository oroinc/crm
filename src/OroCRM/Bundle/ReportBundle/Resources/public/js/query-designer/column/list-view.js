/* global define */
define(['underscore', 'backbone', 'oro/query-designer/column/collection', 'oro/query-designer/column/view'],
function(_, Backbone, __, ColumnCollection, ColumnView) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/query-designer/column/list-view
     * @class   oro.queryDesigner.column.ListView
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Object} */
        options: {
            collection: null,
            containerSelector: null,
            itemTemplateSelector: null,
            itemFormSelector: null,
            itemFormValidationScriptUrl: null
        },

        initialize: function() {
            this.options.collection = this.options.collection || new ColumnCollection();

            this.listenTo(this.model, 'destroy', this.onModelDelete);
        },

        getContainer: function() {
            return $(this.options.containerSelector);
        },

        getCollection: function() {
            return this.options.collection;
        },

        onModelAdded: function(model) {
            var view = new ColumnView({
                templateSelector: this.options.itemTemplateSelector
            });
            view.model = model;
            this.getContainer().append(view.render().el);
        },

        onModelDelete: function() {
            var selector = '[data-id="' + this.model.id + '"]';
            this.getContainer().find(selector).remove();
            this.remove();
        },

        render: function() {
            this.getContainer().empty();
            this.getCollection().each(_.bind(function (model) {
                this.onModelAdded(model);
            }, this));

            return this;
        }
    });
});
