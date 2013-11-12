/* global define */
define(['underscore', 'backbone',
    'oro/query-designer/column/collection', 'oro/query-designer/column/model', 'oro/query-designer/column/view', 'oro/query-designer/column/list-view'],
function(_, Backbone,
         ColumnCollection, ColumnModel, ColumnView, ColumnListView) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/query-designer
     * @class   oro.QueryDesigner
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Object} */
        options: {
            columnsOptions: {
                collection: null,
                containerSelector: null,
                itemTemplateSelector: null,
                itemFormSelector: null
            },
            filtersOptions: {
                collection: null,
                containerTemplateSelector: null
            }
        },

        /** @property */
        columnsView: ColumnListView,

        initialize: function() {
        },

        initializeColumnsView: function () {
            this.columnsView = new this.columnsView(this.options.columnsOptions);
            delete this.options.columnsOptions;
        },

        render: function() {
            this.initializeColumnsView();

            return this;
        }
    });
});
