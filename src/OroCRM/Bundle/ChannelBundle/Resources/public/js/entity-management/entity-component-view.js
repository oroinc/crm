/*jslint vars: true, nomen: true, browser: true*/
/*jshint browser: true*/
/*global define, require*/
define(function (require) {
    'use strict';

    var Backbone = require('backbone'),
        _ = require('underscore'),
        __ = require('orotranslation/js/translator'),
        EntityModel = require('./model'),
        componentTemplate = require('text!./templates/component.html'),
        entityTemplate = require('text!./templates/entity-item.html'),
        formTemplate = require('text!./templates/form.html');

    require('oroui/js/items-manager/editor');

    var modes = {
        VIEW_MODE: 1,
        EDIT_MODE: 2
    };

    /**
     * @class   orocrmchannel.entityManagement.EntityComponentView
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
            metadata: null
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
        initialize: function (options) {
            this.options = _.defaults(options || {}, this.options);
            if (!this.options.metadata) {
                throw new Error('Missing "metadata" options for entity selection compoment');
            }

            var models = this.options.data.length > 0 ? _.map(this.options.data, _.bind(this._createModel, this)) : [];
            this.collection = this.collection || new (Backbone.Collection)(models, {model: EntityModel});

            this.listenTo(this.collection, 'add', this._onItemAdded);
            this.listenTo(this.collection, 'remove', this.renderList);
            this.listenTo(this.collection, 'reset', this.renderList);
        },

        /**
         * Renders component
         */
        render: function () {
            var templateContext = {__: __};

            this.$el.html(this.template(_.extend({}, templateContext)));

            this.$formContainer   = this.$el.find('.form-container');
            this.$listContainer   = this.$el.find('.grid-container');
            this.$noDataContainer = this.$el.find('.no-data');

            if (this.options.mode === modes.EDIT_MODE) {
                this.$formContainer.html(this.formTemplate(_.extend({}, templateContext)));
                _.defer(_.bind(this._initializeForm, this));
            }
            this.renderList(this.collection.models);

            return this;
        },

        /**
         * Renders list into item container
         *
         * @param {Array.<orocrmchannel.entityManagement.Model>} models
         */
        renderList: function (models) {
            var $itemsContainer = this.$listContainer.find('tbody');
            $itemsContainer.empty();

            if(models.length > 0){
                _.each(models, _.bind(function (model) {
                    $itemsContainer.append(this._renderItem(model));
                }, this));

                this._hideEmptyMessage();
            } else {
                this._showEmptyMessage();
            }
        },

        /**
         * Renders single item
         *
         * @param {orocrmchannel.entityManagement.Model} model
         * @returns {string}
         * @private
         */
        _renderItem: function (model) {
            var context = _.extend({actions: [], __: __}, model.toJSON());

            return this.itemTemplate(context);
        },

        /**
         * Appends single item to list
         *
         * @param {Object.<orocrmchannel.entityManagement.Model>} model
         * @private
         */
        _onItemAdded: function(model) {
            model.set(this._prepareModelAttributes(model));

            var $itemsContainer = this.$listContainer.find('tbody');
            $itemsContainer.append(this._renderItem(model));
            this._hideEmptyMessage();
        },

        /**
         * Initialize form component
         *
         * @private
         */
        _initializeForm: function () {
            this.$formContainer.find('[data-purpose="entity-selector"]').select2({
                placeholder: __('orocrm.channel.form.entity'),
                data: _.bind(function () {
                    var notSelected = _.filter(this.options.metadata, _.bind(function(entityMetadata) {
                        return !this.collection.findWhere({name: entityMetadata.name});
                    }, this)),

                        choices = _.map(notSelected, function(entityMetadata) {
                            return {id: entityMetadata.name, text: entityMetadata.label};
                        });

                    return {more: false, results: choices};
                }, this)
            });

            this.$formContainer.itemsManagerEditor($.extend({}, {
                collection: this.collection
            }));
        },

        /**
         * Hide list and show "No data" message
         *
         * @private
         */
        _showEmptyMessage: function () {
            this.$listContainer.hide();
            this.$noDataContainer.show();
        },

        /**
         * Hide "No data" message and show list
         *
         * @private
         */
        _hideEmptyMessage: function () {
            this.$listContainer.show();
            this.$noDataContainer.hide();
        },

        /**
         * Prepares model attributes
         *
         * @param   {Object.<orocrmchannel.entityManagement.Model>} model
         * @returns {object}
         * @private
         */
        _prepareModelAttributes: function (model) {
            var entityName = model.get('name'),
                entityMetadata = this.options.metadata[entityName] || {};

            return _.defaults(entityMetadata, {name: entityName, label: entityName});
        },

        /**
         * Creates model form name
         *
         * @param   {string} entityName
         * @returns {Object.<orocrmchannel.entityManagement.Model>}
         * @private
         */
        _createModel: function (entityName) {
            var model = new EntityModel({name: entityName});
            model.set(this._prepareModelAttributes(model));

            return model;
        }
    });
});
