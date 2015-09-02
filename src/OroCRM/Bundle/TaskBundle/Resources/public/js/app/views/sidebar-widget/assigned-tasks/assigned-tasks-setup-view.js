define(function(require) {
    'use strict';

    var AssignedTasksSetuoView;
    var _ = require('underscore');
    var BaseView = require('oroui/js/app/views/base/view');

    AssignedTasksSetuoView = BaseView.extend({
        template: require('tpl!orocrmtask/templates/sidebar-widget/assigned-tasks/assigned-tasks-setup-view.html'),

        events: {
            'keyup input': function(e) {
                if (e.which === 13) {
                    this.onSubmit(e);
                }
            }
        },

        listen: {
            'ok': 'onSubmit'
        },

        onSubmit: function() {
            var model = this.model;
            var settings = model.get('settings');
            var perPageEl = this.$el.find('[name="perPage"]');
            var perPage = parseInt(perPageEl.val());

            if (_.isNaN(perPage) || perPage <= 0) {
                perPageEl.val(settings.perPage || this.defaultPerPage);
                perPageEl.focus();
                return;
            }

            if (settings.perPage !== perPage) {
                settings.perPage = perPage;
                model.set({settings: settings}, {silent: true});
                model.trigger('change');
            }

            this.trigger('close');
        }
    });

    return AssignedTasksSetuoView;
});
