define(function(require) {
    'use strict';

    var AssignedTasksSetupView;
    var __ = require('orotranslation/js/translator');
    var BaseWidgetSetupView = require('orosidebar/js/app/views/base-widget/base-widget-setup-view');

    AssignedTasksSetupView = BaseWidgetSetupView.extend({
        template: require('tpl!orocrmtask/templates/sidebar-widget/assigned-tasks/assigned-tasks-setup-view.html'),

        widgetTitle: function() {
            return __('orocrm.task.assigned_tasks_widget.settings');
        },

        validation: {
            perPage: {
                NotBlank: {},
                Regex: {pattern: '/^\\d+$/'},
                Number: {min: 1, max: 20}
            }
        },

        fetchFromData: function() {
            var data = AssignedTasksSetupView.__super__.fetchFromData.call(this);
            data.perPage = Number(data.perPage);
            return data;
        }
    });

    return AssignedTasksSetupView;
});
