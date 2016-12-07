define(function(require) {
    'use strict';

    var Backbone = require('backbone');
    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var EntityModel = require('./model');
    var componentTemplate = require('text!./templates/component.html');
    var entityTemplate = require('text!./templates/entity-item.html');
    var formTemplate = require('text!./templates/form.html');
    var entitySelectResultTemplate = require('text!./templates/select2/result.html');
    var entitySelectSelectionTemplate = require('text!./templates/select2/selection.html');
    var Select2Component = require('oro/select2-component');

    require('oroui/js/items-manager/editor');
    require('oroui/js/items-manager/table');

    var modes = {
        VIEW_MODE: 1,
        EDIT_MODE: 2
    };

    /**
     * @class   orochannel.entityManagement.EntityComponentView
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /**
         * Widget mode constants
         *
         * @const
         */
        MODES: _.clone(modes),

        /**
         * @type {function(Object)}
         */
        template: _.template(componentTemplate),

        /**
         * @type {function(Object)}
         */
        itemTemplate: _.template(entityTemplate),

        /**
         * @type {function(Object)}
         */
        formTemplate: _.template(formTemplate),

        /**
         * @type {object}
         */
        options: {
            data: [],
            mode: modes.VIEW_MODE,
            entityModel: EntityModel,
            metadata: null,
            lockedEntities: []
        },

        /**
         * @type {Backbone.Collection}
         */
        collection: null,

        /**
         * @type {jQuery}
         */
        $formContainer: null,

        /**
         * @type {jQuery}
         */
        $listContainer: null,

        /**
         * @type {jQuery}
         */
        $noDataContainer: null,

        /**
         * Initialize view
         *
         * @param {object} options
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);
            if (!this.options.metadata) {
                throw new Error('Missing "metadata" options for entity selection compoment');
            }

            var models = this.options.data.length > 0 ? _.map(this.options.data, _.bind(this._createModel, this)) : [];
            this.collection = this.collection || new (Backbone.Collection)(models, {model: EntityModel});
            this.listenTo(this.collection, 'add remove reset', this._onCollectionChange);
            this.listenTo(this.collection, 'add', this._onItemAdded);
        },

        /**
         * Renders component
         */
        render: function() {
            var templateContext = {__: __};

            this.$el.html(this.template(_.extend({}, templateContext)));
            this.$formContainer = this.$el.find('.form-container');
            this.$listContainer = this.$el.find('.grid-container');
            this.$noDataContainer = this.$el.find('.no-data');

            if (this.options.mode === modes.EDIT_MODE) {
                this.$formContainer.html(this.formTemplate(_.extend({}, templateContext)));
                _.defer(_.bind(this._initializeForm, this));
            }

            _.defer(_.bind(this._initializeList, this));
        },

        /**
         * Initialize form component
         *
         * @private
         */
        _initializeForm: function() {
            var configs = {
                    placeholder:        __('oro.channel.form.entity'),
                    result_template:    entitySelectResultTemplate,
                    selection_template: entitySelectSelectionTemplate,
                    data: _.bind(function() {
                        var notSelected = _.omit(this.options.metadata, this.collection.pluck('name'));
                        var options = _.map(notSelected, function(entityMetadata) {
                            return {
                                id: entityMetadata.name,
                                text: entityMetadata.label,
                                icon: entityMetadata.icon,
                                type: entityMetadata.type
                            };
                        });
                        var optionGroups = _.groupBy(options, function(entityMetadata) {
                            return entityMetadata.type;
                        });
                        var results = [];

                        _.each(_.keys(optionGroups).sort().reverse(), function(groupName) {
                            results.push({
                                text: __('oro.channel.entity_owner.' + groupName),
                                icon: null,
                                children: optionGroups[groupName]
                            });
                        });

                        return {results: results};
                    }, this)
                };
            var $el = this.$formContainer.find('[data-purpose="entity-selector"]');
            var select2Component = new Select2Component({
                configs: configs,
                _sourceElement: $el
            });
            this.pageComponent('entity-selector', select2Component, $el);
            this.$formContainer.itemsManagerEditor({
                collection: this.collection
            });
        },

        /**
         * Initialize list component
         *
         * @private
         */
        _initializeList: function() {
            this.$listContainer.find('tbody').itemsManagerTable({
                collection:   this.collection,
                itemTemplate: this.itemTemplate,
                itemRender: function itemRenderer(template, data) {
                    var context = _.extend({__: __}, data);

                    return template(context);
                },
                deleteHandler: _.partial(function(collection, model, data) {
                    collection.remove(model);
                }, this.collection),
                sorting: false
            });
            // emulate reset for first time
            this._onCollectionChange();
        },

        /**
         * Collection change handler. Shows/Hides empty message
         *
         * @private
         */
        _onCollectionChange: function() {
            if (!this.collection.isEmpty()) {
                this.$listContainer.show();
                this.$noDataContainer.hide();
            } else {
                this.$listContainer.hide();
                this.$noDataContainer.show();
            }
        },

        /**
         * Appends single item to list
         *
         * @param {Object.<orochannel.entityManagement.Model>} model
         * @private
         */
        _onItemAdded: function(model) {
            model.set(this._prepareModelAttributes(model));
        },

        /**
         * Prepares model attributes
         *
         * @param   {Object.<orochannel.entityManagement.Model>} model
         * @returns {object}
         * @private
         */
        _prepareModelAttributes: function(model) {
            var entityName = model.get('name');
            var entityMetadata = this.options.metadata[entityName] || {};
            var actions = [];
            var lockedEntities = this.options.lockedEntities;

            if ((entityName.indexOf(lockedEntities) === -1) && this.options.mode === modes.EDIT_MODE) {
                actions.push({
                    collectionAction: 'delete',
                    title: 'Delete',
                    icon: 'fa-trash-o'
                });
            } else if (this.options.mode === modes.VIEW_MODE) {
                actions.push({
                    title: 'View',
                    icon:  'fa-eye',
                    url:   entityMetadata.view_link
                });
                actions.push({
                    title: 'Edit',
                    icon:  'fa-pencil-square-o',
                    url:   entityMetadata.edit_link
                });
            }

            return _.defaults(entityMetadata, {name: entityName, label: entityName, actions: actions});
        },

        /**
         * Creates model form name
         *
         * @param   {string} entityName
         * @returns {Object.<orochannel.entityManagement.Model>}
         * @private
         */
        _createModel: function(entityName) {
            var model = new EntityModel({name: entityName});
            model.set(this._prepareModelAttributes(model));

            return model;
        }
    });
});
