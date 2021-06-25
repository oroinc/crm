define(function(require) {
    'use strict';

    const Backbone = require('backbone');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const EntityModel = require('./model');
    const componentTemplate = require('text-loader!./templates/component.html');
    const entityTemplate = require('text-loader!./templates/entity-item.html');
    const formTemplate = require('text-loader!./templates/form.html');
    const entitySelectResultTemplate = require('text-loader!./templates/select2/result.html');
    const entitySelectSelectionTemplate = require('text-loader!./templates/select2/selection.html');
    const Select2Component = require('oro/select2-component');

    require('oroui/js/items-manager/editor');
    require('oroui/js/items-manager/table');

    const modes = {
        VIEW_MODE: 1,
        EDIT_MODE: 2
    };

    /**
     * @class   orochannel.entityManagement.EntityComponentView
     * @extends Backbone.View
     */
    const EntityComponentView = Backbone.View.extend({
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
         * @inheritdoc
         */
        constructor: function EntityComponentView(options) {
            EntityComponentView.__super__.constructor.call(this, options);
        },

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

            const models = this.options.data.length > 0
                ? _.map(this.options.data, this._createModel.bind(this)) : [];
            this.collection = this.collection || new (Backbone.Collection)(models, {model: EntityModel});
            this.listenTo(this.collection, 'add remove reset', this._onCollectionChange);
            this.listenTo(this.collection, 'add', this._onItemAdded);
        },

        /**
         * Renders component
         */
        render: function() {
            const templateContext = {__: __};

            this.$el.html(this.template(_.extend({}, templateContext)));
            this.$formContainer = this.$el.find('.form-container');
            this.$listContainer = this.$el.find('.grid-container');
            this.$noDataContainer = this.$el.find('.no-data');

            if (this.options.mode === modes.EDIT_MODE) {
                this.$formContainer.html(this.formTemplate(_.extend({}, templateContext)));
                _.defer(this._initializeForm.bind(this));
            }

            _.defer(this._initializeList.bind(this));
        },

        /**
         * Initialize form component
         *
         * @private
         */
        _initializeForm: function() {
            const configs = {
                placeholder: __('oro.channel.form.entity'),
                result_template: entitySelectResultTemplate,
                selection_template: entitySelectSelectionTemplate,
                data: () => {
                    const notSelected = _.omit(this.options.metadata, this.collection.pluck('name'));
                    const options = _.map(notSelected, function(entityMetadata) {
                        return {
                            id: entityMetadata.name,
                            text: entityMetadata.label,
                            icon: entityMetadata.icon,
                            type: entityMetadata.type
                        };
                    });
                    const optionGroups = _.groupBy(options, function(entityMetadata) {
                        return entityMetadata.type;
                    });
                    const results = [];

                    _.each(_.keys(optionGroups).sort().reverse(), function(groupName) {
                        results.push({
                            text: __('oro.channel.entity_owner.' + groupName),
                            icon: null,
                            children: optionGroups[groupName]
                        });
                    });

                    return {results: results};
                }
            };
            const $el = this.$formContainer.find('[data-purpose="entity-selector"]');
            const select2Component = new Select2Component({
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
                collection: this.collection,
                itemTemplate: this.itemTemplate,
                itemRender: function itemRender(template, data) {
                    const context = _.extend({__: __}, data);

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
            const entityName = model.get('name');
            const entityMetadata = this.options.metadata[entityName] || {};
            const actions = [];
            const lockedEntities = this.options.lockedEntities;

            if ((entityName.indexOf(lockedEntities) === -1) && this.options.mode === modes.EDIT_MODE) {
                actions.push({
                    collectionAction: 'delete',
                    title: 'Delete',
                    icon: 'fa-trash-o'
                });
            } else if (this.options.mode === modes.VIEW_MODE) {
                actions.push({
                    title: 'View',
                    icon: 'fa-eye',
                    url: entityMetadata.view_link
                });
                actions.push({
                    title: 'Edit',
                    icon: 'fa-pencil-square-o',
                    url: entityMetadata.edit_link
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
            const model = new EntityModel({name: entityName});
            model.set(this._prepareModelAttributes(model));

            return model;
        }
    });

    return EntityComponentView;
});
