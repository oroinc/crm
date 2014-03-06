/*jslint nomen: true, vars: true*/
/*global define*/

define(['jquery', 'underscore', 'backbone', 'routing'],
    function ($, _, Backbone, routing) {
        return { ContentView: Backbone.View.extend({
            template: _.template('<div class="tasks-list-wrapper" style="position: relative;"><%= content %></div>'),

            initialize: function () {
                var view = this;
                view.listenTo(view.model, 'change', view.render);
            },

            render: function () {
                var view = this;

                $.get(routing.generate('orocrm_task_widget_sidebar_tasks'), {}, function(content){
                    view.$el.html(view.template({'content':content}));
                });
                return view;
            }
        }), SetupView: Backbone.View.extend({
            template: _.template('Config'),

            initialize: function () {
                var view = this;
                view.listenTo(view.model, 'change', view.render);
            },

            render: function () {
                var view = this;
                view.$el.html(view.template(view.model.toJSON()));
                return view;
            }
        })};
    }
);
