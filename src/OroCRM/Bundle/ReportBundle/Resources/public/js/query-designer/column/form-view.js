/* global define */
define(['underscore', 'backbone', 'oro/app', 'oro/messenger', 'oro/query-designer/column/model'],
function(_, Backbone, app, messenger, ColumnModel) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/query-designer/column/form-view
     * @class   oro.queryDesigner.column.FormView
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Object} */
        options: {
            addButtonSelector: null
        },

        /** @property ColumnModel */
        model: ColumnModel,

        initialize: function() {
            // subscribe to Add button click event
            var onAdd = _.bind(function (e) {
                e.preventDefault();
                this.saveModel();
            }, this);
            this.$el.find(this.options.addButtonSelector).on('click', onAdd);
        },

        saveModel: function() {
            try {
                var data = this.getFormData();
                this.model.save(data);
            } catch (err) {
                this.showError(err);
            }
        },

        showError: function (err) {
            if (!_.isUndefined(console)) {
                console.error(_.isUndefined(err.stack) ? err : err.stack);
            }
            var msg = message;
            if (app.debug) {
                if (!_.isUndefined(err.message)) {
                    msg += ': ' + err.message;
                } else if (!_.isUndefined(err.errors) && _.isArray(err.errors)) {
                    msg += ': ' + err.errors.join();
                } else if (_.isString(err)) {
                    msg += ': ' + err;
                }
            }
            messenger.notificationFlashMessage('error', msg);
        },

        getFormData: function () {
            var fieldNameRegex = /\[(\w+)\]$/;
            var data = {};
            var formData = this.$el.serializeArray();
            _.each(formData, function (dataItem) {
                var fieldNameData = fieldNameRegex.exec(dataItem.name);
                if (fieldNameData && fieldNameData.length == 2) {
                    data[fieldNameData[1]] = dataItem.value;
                }
            });

            return data;
        }
    });
});
