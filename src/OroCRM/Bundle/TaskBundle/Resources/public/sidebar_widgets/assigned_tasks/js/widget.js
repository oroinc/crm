/*jslint nomen: true, vars: true*/
/*global define*/
define(['jquery', 'underscore', 'backbone', 'routing', 'oronavigation/js/navigation', 'oroui/js/loading-mask'],
    function ($, _, Backbone, routing, Navigation, LoadingMask) {
        /**
         * @export  orotask/widget/assigned-task
         */
        return {
            ContentView: Backbone.View.extend({
                defaultPerPage: 5,

                template: _.template('<div class="tasks-list-wrapper"><%= content %></div>'),

                events: {
                    'click .task-widget-row': 'onClickTask'
                },

                initialize: function () {
                    this.on('refresh', this.reloadTasks);
                    Backbone.View.prototype.initialize.apply(this, arguments);
                },

                render: function () {
                    this.reloadTasks();
                    return this;
                },

                onClickTask: function (event) {
                    var taskUrl = $(event.currentTarget).data('url');
                    var navigation = Navigation.getInstance();
                    if (navigation) {
                        navigation.setLocation(taskUrl);
                    }
                },

                reloadTasks: function () {
                    var view = this;
                    var settings = this.model.get('settings');
                    settings.perPage = settings.perPage || this.defaultPerPage;

                    var routeParams = { perPage: settings.perPage, r: Math.random() };
                    var url = routing.generate('orocrm_task_widget_sidebar_tasks', routeParams);

                    var loadingMask = new LoadingMask();
                    view.$el.html('<div class="widget-mask-wrapper"></div>');
                    view.$el.find('.widget-mask-wrapper').append(loadingMask.render().$el);
                    loadingMask.show();

                    $.get(url, function (content) {
                        view.$el.html(view.template({'content': content}));
                        loadingMask.hide();
                    });
                }
            }),

            SetupView: Backbone.View.extend({
                template: _.template(
                    '<h3><%= _.__("orocrm.task.assigned_tasks_widget.settings") %></h3>' +
                    '<label for="perPage"><%= _.__("orocrm.task.assigned_tasks_widget.number_of_tasks") %></label>' +
                    '<input type="text" name="perPage" value="<%= settings.perPage %>"/>'
                ),

                events: {
                    'keyup input': function (e) {
                        if (e.which === 13) {
                            this.onSubmit(e);
                        }
                    }
                },

                initialize: function () {
                    this.on('ok', this.onSubmit);
                    Backbone.View.prototype.initialize.apply(this, arguments);
                },

                render: function () {
                    var view = this;
                    view.$el.html(view.template(view.model.toJSON()));
                    return view;
                },

                onSubmit: function () {
                    var model = this.model;
                    var settings = model.get('settings');
                    var perPageEl = this.$el.find('[name="perPage"]');
                    var perPage = parseInt(perPageEl.val());

                    if (_.isNaN(perPage) || perPage <= 0) {
                        perPageEl.val(settings.perPage || this.defaultPerPage);
                        perPageEl.focus();
                        return;
                    }

                    if (settings.perPage != perPage) {
                        settings.perPage = perPage;
                        model.set({ settings: settings }, { silent: true });
                        model.trigger('change');
                    }

                    this.trigger('close');
                }
            })
        };
    }
);
