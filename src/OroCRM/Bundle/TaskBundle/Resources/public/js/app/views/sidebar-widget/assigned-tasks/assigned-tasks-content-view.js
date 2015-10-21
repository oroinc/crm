define(function(require) {
    'use strict';

    var AssignedTasksContentView;
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');
    var routing = require('routing');
    var LoadingMask = require('oroui/js/app/views/loading-mask-view');
    var BaseView = require('oroui/js/app/views/base/view');

    AssignedTasksContentView = BaseView.extend({
        defaultPerPage: 5,

        template: require('tpl!orocrmtask/templates/sidebar-widget/assigned-tasks/assigned-tasks-content-view.html'),

        events: {
            'click .task-widget-row': 'onClickTask'
        },

        listen: {
            refresh: 'reloadTasks'
        },

        render: function() {
            this.reloadTasks();
            return this;
        },

        onClickTask: function(event) {
            var taskUrl = $(event.currentTarget).data('url');
            mediator.execute('redirectTo', {url: taskUrl});
        },

        reloadTasks: function() {
            var view = this;
            var settings = this.model.get('settings');
            settings.perPage = settings.perPage || this.defaultPerPage;

            var routeParams = {
                perPage: settings.perPage,
                r: Math.random()
            };
            var url = routing.generate('orocrm_task_widget_sidebar_tasks', routeParams);

            var loadingMask = new LoadingMask({
                container: view.$el
            });
            loadingMask.show();

            $.get(url, function(content) {
                loadingMask.dispose();
                view.$el.html(view.template({'content': content}));
            });
        }
    });

    return AssignedTasksContentView;
});
