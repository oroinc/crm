/* global define */
define(['underscore', 'backbone', 'oro/translator', 'oro/app', 'oro/messenger', 'oro/delete-confirmation',
    'oro/query-designer/column/collection', 'oro/query-designer/column/model',
    'jquery-outer-html'],
function(_, Backbone, __, app, messenger, DeleteConfirmation,
         ColumnCollection, ColumnModel) {
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
            collection: null,
            updateStorage: function (data) { },
            containerSelector: null,
            itemTemplateSelector: null,
            itemFormSelector: null
        },

        /** @property {Object} */
        selectors: {
            cancelButton:   '.cancel-button',
            saveButton:     '.save-button',
            addButton:      '.add-button',
            editButton:     '.edit-button',
            deleteButton:   '.delete-button'
        },

        initialize: function() {
            this.options.collection = this.options.collection || new ColumnCollection();

            this.itemTemplate = _.template($(this.options.itemTemplateSelector).html());

            // subscribe to collection events
            this.listenTo(this.getCollection(), 'add', this.onModelAdded);
            this.listenTo(this.getCollection(), 'change', this.onModelChanged);
            this.listenTo(this.getCollection(), 'remove', this.onModelDeleted);
        },

        getCollection: function() {
            return this.options.collection;
        },

        getContainer: function() {
            return $(this.options.containerSelector);
        },

        addModel: function(model) {
            model.set('id', _.uniqueId('column'));
            this.getCollection().add(model);
        },

        deleteModel: function(model) {
            this.getCollection().remove(model);
        },

        onModelAdded: function(model) {
            var data = model.toJSON();
            _.each(data, _.bind(function (value, key) {
                data[key] = this.getLocalizedText(key, value);
            }, this));
            var item = $(this.itemTemplate(data));
            this.bindItemActions(item);
            this.getContainer().append(item);
            this.updateStorage();
        },

        onModelChanged: function(model) {
            var data = model.toJSON();
            _.each(data, _.bind(function (value, key) {
                data[key] = this.getLocalizedText(key, value);
            }, this));
            var item = $(this.itemTemplate(data));
            this.bindItemActions(item);
            this.getContainer().find('[data-id="' + model.id + '"]').outerHTML(item);
            this.updateStorage();
        },

        onModelDeleted: function(model) {
            this.getContainer().find('[data-id="' + model.id + '"]').remove();
            this.updateStorage();
        },

        updateStorage: function () {
            var data = this.getCollection().toJSON();
            _.each(data, function (value) {
                delete value.id;
            });
            this.options.updateStorage(data);
        },

        handleAddModel: function() {
            var model = new ColumnModel();
            var keys = _.keys(model.attributes);
            var data = this.getFormData(keys);
            this.clearFormData(keys);
            model.set(data);
            this.addModel(model);
        },

        handleSaveModel: function(modelId) {
            var model = this.getCollection().get(modelId);
            var keys = _.keys(model.attributes);
            var data = this.getFormData(keys);
            this.clearFormData(keys);
            this.toggleFormButtons(null);
            model.set(data);
        },

        handleDeleteModel: function(modelId) {
            var model = this.getCollection().get(modelId);
            if (this.$el.find(this.selectors.saveButton).data('id') == modelId) {
                var keys = _.keys(model.attributes);
                this.clearFormData(keys);
                this.toggleFormButtons(null);
            }
            this.deleteModel(model);
        },

        handleCancelButton: function(modelId) {
            var model = this.getCollection().get(modelId);
            this.clearFormData(_.keys(model.attributes));
            this.toggleFormButtons(null);
        },

        toggleFormButtons: function (modelId) {
            if (_.isNull(modelId)) {
                modelId = '';
            }
            var addButton = this.$el.find(this.selectors.addButton);
            var saveButton = this.$el.find(this.selectors.saveButton);
            var cancelButton = this.$el.find(this.selectors.cancelButton);
            saveButton.data('id', modelId);
            cancelButton.data('id', modelId);
            if (modelId == '') {
                cancelButton.hide();
                saveButton.hide();
                addButton.show();
            } else {
                addButton.hide();
                cancelButton.show();
                saveButton.show();
            }
        },

        bindItemActions: function (item) {
            // bind edit button
            var onEdit = _.bind(function (e) {
                e.preventDefault();
                var el = $(e.currentTarget);
                var id = el.closest('[data-id]').data('id');
                var model = this.getCollection().get(id);
                this.setFormData(model.attributes);
                this.toggleFormButtons(id);
            }, this);
            item.find(this.selectors.editButton).on('click', onEdit);

            // bind delete button
            var onDelete = _.bind(function (e) {
                e.preventDefault();
                var el = $(e.currentTarget);
                var id = el.closest('[data-id]').data('id');
                var confirm = new DeleteConfirmation({
                    content: el.data('message')
                });
                confirm.on('ok', _.bind(this.handleDeleteModel, this, id));
                confirm.open();
            }, this);
            item.find(this.selectors.deleteButton).on('click', onDelete);
        },

        render: function() {
            this.form = $(this.options.itemFormSelector);

            var onAdd = _.bind(function (e) {
                e.preventDefault();
                this.handleAddModel();
            }, this);
            this.$el.find(this.selectors.addButton).on('click', onAdd);

            var onSave = _.bind(function (e) {
                e.preventDefault();
                var id = $(e.currentTarget).data('id');
                this.handleSaveModel(id);
            }, this);
            this.$el.find(this.selectors.saveButton).on('click', onSave);

            var onCancel = _.bind(function (e) {
                e.preventDefault();
                var id = $(e.currentTarget).data('id');
                this.handleCancelButton(id);
            }, this);
            this.$el.find(this.selectors.cancelButton).on('click', onCancel);

            this.getContainer().empty();
            this.getCollection().each(_.bind(function (model) {
                this.onModelAdded(model);
            }, this));

            return this;
        },

        getFormData: function (keys) {
            var data = {};
            this.iterateFormData(keys, function (key, el) {
                data[key] = el.val();
            });

            return data;
        },

        clearFormData: function (keys) {
            this.iterateFormData(keys, function (key, el) {
                el.val('');
                el.trigger('change');
            });
        },

        setFormData: function (data) {
            this.iterateFormData(_.keys(data), function (key, el) {
                el.val(data[key]);
                el.trigger('change');
            });
        },

        iterateFormData: function (keys, callback) {
            keys = _.without(keys, 'id');
            var fieldNameRegex = /\[(\w+)\]$/;
            var elements = this.form.find('[name]');
            _.each(elements, function (el) {
                var fieldNameData = fieldNameRegex.exec(el.name);
                if (fieldNameData && fieldNameData.length == 2 && _.indexOf(keys, fieldNameData[1]) !== -1) {
                    callback(fieldNameData[1], $(el));
                }
            });
        },

        getLocalizedText: function (key, value) {
            var el = this.form.find('select[name$="\\[' + key + '\\]"] option[value="' + value + '"]');
            return (el.length === 1) ? el.text() : value;
        },

        showError: function (err) {
            if (!_.isUndefined(console)) {
                console.error(_.isUndefined(err.stack) ? err : err.stack);
            }
            var msg = __('Sorry, unexpected error was occurred');
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
        }
    });
});
