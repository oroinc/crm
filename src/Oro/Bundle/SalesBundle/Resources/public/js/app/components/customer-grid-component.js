define(function(require) {
    'use strict';

    var mediator = require('oroui/js/mediator');
    var widgetManager = require('oroui/js/widget-manager');
    var ActivityContextComponent = require('oroactivity/js/app/components/activity-context-component');

    var CustomerGridComponent = ActivityContextComponent.extend({
        /**
         * Handles row selection on a grid
         *
         * @param {} gridWidget
         * @param {} data
         */
        onRowSelect: function(gridWidget, data) {
            var id = data.model.get('id');
            var dialogWidgetName = this.options.dialogWidgetName;
            var customerClass = this.contextView.currentTargetClass();

            mediator.trigger('sales:customer:select', JSON.stringify({'entityClass': customerClass,  'entityId': id}));

            widgetManager.getWidgetInstanceByAlias(dialogWidgetName, function(dialogWidget) {
                dialogWidget.remove();
            });
        }
    });

    return CustomerGridComponent;
});
