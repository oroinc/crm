var Oro = Oro || {};
Oro.windows = Oro.windows || {};

Oro.windows.DialogView = Backbone.View.extend({
    options: {
        type: 'dialog',
        actionsEl: '.widget-actions',
        dialogOptions: null,
        isForm: false,
        url: false
    },

    initialize: function() {
        if (this.options.isForm) {
            var runner = function(handlers) {
                return function() {
                    for (var i = 0; i < handlers.length; i++) if (_.isFunction(handlers[i])) {
                        handlers[i]();
                    }
                }
            };
            this.options.dialogOptions.close = runner([this.revertActions.bind(this), this.options.dialogOptions.close]);
        }
    },

    adoptActions: function() {
        var actionsEl = this._getActionsElement();
        this.hasAdoptedActions = actionsEl.length > 0;
        var widget = this.widget;
        if (this.hasAdoptedActions) {
            actionsEl.hide();
            var form = actionsEl.closest('form');
            var actions = actionsEl.clone(true);
            var container = widget.dialog('actionsContainer');
            actions.find('[type=submit]').each(function(idx, btn) {
                $(btn).click(function() {
                    this.loadContent(form.action, form.method);
                    //$(form).trigger('submit');
                }.bind(this));
            }.bind(this));
            actions.find('[type=reset]').each(function(idx, btn) {
                $(btn).click(function() {
                    $(form).trigger('reset');
                    widget.dialog('close');
                });
            });
            container.empty();
            actions.show();
            actions.appendTo(container);
            widget.dialog('showActionsContainer');
        }
    },

    revertActions: function() {
        if (this.hasAdoptedActions) {
            this._getActionsElement().show();
        }
    },

    _getActionsElement: function() {
        var el = this.options.actionsEl;
        if (typeof el == 'string') {
            el = this.$el.find(el);
        }
        return el;
    },

    render: function() {
        if (this.options.url !== false) {
            this.loadContent(this.options.url);
        } else {
            this.show();
        }
    },

    loadContent: function(url, method) {
        if (typeof url == 'undefined' || !url) {
            url = window.location.href;
        }
        if (typeof method == 'undefined' || !method) {
            method = 'get';
        }
        Backbone.$.ajax({
            url: url,
            type: method
        }).done(function(content) {
            this.setElement(content);
            this.show();
        }.bind(this));
    },

    show: function() {
        if (!this.widget) {
            this.widget = this.$el.dialog(this.options.dialogOptions);
        } else {
            this.widget.html(this.$el);
        }
        this.adoptActions();
    }
});