/*global define*/
define([
    'backbone',
    'underscore',
    './collection',
    'oroui/js/items-manager/table',
    'jquery.select2',
], function (Backbone, _, ItemCollection) {
    'use strict';

    /**
     * @export orocrmmagento/js/metrics/view
     * @class   orocrmmagento.metrics.Model
     * @extends Backbone.Model
     */
    return Backbone.View.extend({
        events: {
            'change .metric-select': '_toggleButtons',
            'click .add-button:not(.disabled)': '_onAddClick',
            'click .add-all-button:not(.disabled)': '_onAddAllClick',
        },

        requiredOptions: [
            'metricsData',
            'baseName',
        ],

        items: null,
        metricSelect: null,

        initialize: function (options) {
            _.each(this.requiredOptions, function (optionName) {
                if (!_.has(options, optionName)) {
                    throw new Error('Required option "' + optionName + '" not found.');
                }
            });

            this.items = this._initializeItems(options.metricsData, options.baseName);

            this._initializeFilter(this.items);
            this._initializeItemGrid(this.items);
            this._fixConfigurationWindow();
            this._toggleButtons();
        },

        _initializeItems: function (metricsData, baseName) {
            var items = new ItemCollection(metricsData);
            items.each(function (item, index) {
                item.set('namePrefix', baseName + '[' + index + ']');
            });

            return items;
        },

        _initializeFilter: function (items) {
            var selectTpl = _.template($('#magento-big-numbers-metric-select-template').html());
            var select = selectTpl({
                metrics: items,
            });

            var $filterContainer = this.$('.controls');
            $filterContainer.prepend(select);
            this.metricSelect = $filterContainer.find('select');
            this.metricSelect.select2({
                allowClear: true,
            });

            items.on('change:show', function (model) {
                var $option = this.metricSelect.find('option[value=' + model.id + ']');
                model.get('show') ? $option.addClass('hide') : $option.removeClass('hide');
            }, this);

            var showedItems = items.where({show: true});
            _.each(showedItems, function (item) {
                var $option = this.metricSelect.find('option[value=' + item.id + ']');
                $option.addClass('hide');
            }, this);
        },

        _initializeItemGrid: function (items) {
            var $itemContainer = this.$('.item-container');
            var showedItems = items.where({show: true});
            var filteredItems = new ItemCollection(showedItems);

            $itemContainer.itemsManagerTable({
                itemTemplate: $('#magento-big-numbers-metric-template').html(),
                collection: filteredItems,
            });

            filteredItems.on('sort add', function () {
                $itemContainer.find('input.order').each(function (index) {
                    $(this).val(index).trigger('change');
                });
            });

            filteredItems.on('action:delete', function (model) {
                model.set('show', false);
            });

            items.on('change:show', function (model) {
                model.get('show') ? filteredItems.add(model) : filteredItems.remove(model);
            });

            $itemContainer.on('change', function (e) {
                var $target = $(e.target);
                var item = items.get($target.closest('tr').data('cid'));
                var value = $target.is(':checkbox') ? $target.is(':checked') : $target.val();
                item.set($target.data('name'), value);
            });
        },

        _fixConfigurationWindow: function () {
            var $scrollable = this.$('.scrollable-container');
            var $widgetContent = this.$el.closest('.widget-content');
            var $widget = this.$el.closest('.ui-widget');

            var optimumEnlargement = $scrollable.prop('scrollHeight') - $scrollable.height();
            var allowedEnlargement = $(window).outerHeight() - $widget.outerHeight();

            var enlargement = Math.min(optimumEnlargement, allowedEnlargement);
            if (!enlargement) {
                return;
            }

            $widgetContent.height($widgetContent.height() + enlargement);
            this.$el.closest('div.widget-configuration').trigger('dialogresize');
        },

        _onAddClick: function () {
            var metric = this.metricSelect.select2('val');
            var model = this.items.get(metric);
            model.set('show', true);
            this.metricSelect.select2('val', '').change();
        },

        _onAddAllClick: function () {
            this.items.each(function (item) {
                item.set('show', true);
            });
            this.metricSelect.select2('val', '').change();
        },

        _toggleButtons: function () {
            if (this.metricSelect.select2('val')) {
                this.$('.add-button').removeClass('disabled');
            } else {
                this.$('.add-button').addClass('disabled');
            }
        }
    });
});
