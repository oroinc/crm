/*jslint nomen: true, vars: true*/
/*global define*/
define(['jquery', 'underscore', 'backbone', 'routing', 'oroui/js/loading-mask'],
    function ($, _, Backbone, routing, LoadingMask) {
        return { ContentView: Backbone.View.extend({
            template: _.template('<div class="tasks-list-wrapper"><%= content %></div>'),
            render: function () {
                this.reloadTasks(true);
                return this;
            },
            reloadTasks: function(fromCache){
                var view = this;
                var settings = this.model.get('settings');
                settings.perPage = settings.perPage ? settings.perPage : 10;
                var url = routing.generate('orocrm_task_widget_sidebar_tasks', { 'perPage': settings.perPage });

                if(!fromCache){
                    url+="#"+Math.random();
                }

                var loadingMask = new LoadingMask();
                view.$el.html('<div class="widget-mask-wrapper"></div>');
                view.$el.find('.widget-mask-wrapper').append(loadingMask.render().$el);
                loadingMask.show();

                require(["text!"+url], function (content) {
                    view.$el.html(view.template({'content': content}));
                    loadingMask.hide();
                    view.$el.find('.refresh-task-widget').click(function(){
                        view.reloadTasks(false);
                    });
                });
            }
        }), SetupView: Backbone.View.extend({
            template: _.template(
                '<h3><%= _.__("orocrm.task.assigned_tasks_widget.settings") %></h3>' +
                    '<label for="perPage"><%= _.__("orocrm.task.assigned_tasks_widget.number_of_tasks") %></label>' +
                    '<input type="text" name="perPage" value="<%= settings.perPage %>"/>'
            ),

            events: {
                'change [name="perPage"]': 'onChange'
            },

            render: function () {
                var view = this;
                view.$el.html(view.template(view.model.toJSON()));
                return view;
            },

            onChange: function (e) {
                var model = this.model;
                var perPage = this.$el.find('[name="perPage"]').val();
                var settings = model.get('settings');
                settings.perPage = perPage;
                model.set({ settings: settings }, { silent: true });
                model.trigger('change');
            }
        })
        };
    }
);
