/* global define */
define(['underscore', 'backbone',
    'oro/query-designer/column/collection', 'oro/query-designer/column/model', 'oro/query-designer/column/view'],
function(_, Backbone,
         ColumnCollection, ColumnModel, ColumnView) {
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
            storageSelector: null,
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
        columnsView: ColumnView,

        initialize: function() {
        },

        updateColumnStorage: function (columns) {
            var data = {
                columns: columns
            };
            $(this.options.storageSelector).val(JSON.stringify(data));
        },

        render: function() {
            // initialize columns view
            _.extend(this.options.columnsOptions, {
                updateStorage: _.bind(this.updateColumnStorage, this)
            });
            this.columnsView = new this.columnsView(this.options.columnsOptions);
            delete this.options.columnsOptions;
            this.columnsView.render();

            return this;
        }
    });
});
