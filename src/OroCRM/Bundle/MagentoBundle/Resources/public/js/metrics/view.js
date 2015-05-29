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
            'change .select-all': '_onSelectAll',
        },

        requiredOptions: [
            'metricsData',
            'baseName',
        ],

        initialize: function (options) {
            _.each(this.requiredOptions, function (optionName) {
                if (!_.has(options, optionName)) {
                    throw new Error('Required option "' + optionName + '" not found.');
                }
            });

            var items = this._initializeItems(options.metricsData, options.baseName);

            var filteredItems = items.clone();
            this._initializeFilter(items, filteredItems);
            this._initializeItemGrid(items, filteredItems);
            this._fixConfigurationWindow();
        },

        _initializeItems: function (metricsData, baseName) {
            var items = new ItemCollection(metricsData);
            items.each(function (item, index) {
                item.set('namePrefix', baseName + '[' + index + ']');
            });

            return items;
        },

        _initializeFilter: function (items, filteredItems) {
            var selectTpl = _.template($('#magento-big-numbers-metric-select-template').html());
            var select = selectTpl({
                metrics: items,
            });

            var $filterContainer = this.$('.controls');
            $filterContainer.html(select);
            $filterContainer.find('select').select2({
                allowClear: true,
            });

            $filterContainer.on('change', function (e) {
                if (e.val) {
                    filteredItems.reset([items.get(e.val)]);
                } else {
                    filteredItems.reset(items.models);
                }
            });
        },

        _initializeItemGrid: function (items, filteredItems) {
            var $itemContainer = this.$('.item-container');

            $itemContainer.itemsManagerTable({
                itemTemplate: $('#magento-big-numbers-metric-template').html(),
                collection: filteredItems,
            });

            filteredItems.on('sort', function () {
                $itemContainer.find('input.order').each(function (index) {
                    $(this).val(index).trigger('change');
                });
                items.reset(filteredItems.models);
            });

            $itemContainer.on('change', function (e) {
                var $target = $(e.target);
                var item = items.get($target.closest('tr').data('cid'));
                var value = $target.is(':checkbox') ? $target.is(':checked') : $target.val();
                item.set($target.data('name'), value);
            });

            $itemContainer.closest('form').on('submit', function () {
                filteredItems.reset(items.models);
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

        _onSelectAll: function (e) {
            var $target = $(e.target);
            this.$('input[data-name=show]').prop('checked', $target.is(':checked')).change();
        }
    });
});
