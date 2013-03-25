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
        this.dialogContent = this.$el.clone(true);
    },

    adoptActions: function() {
        var actions = this._getActionsElement();
        this.hasAdoptedActions = actions.length > 0;
        if (this.hasAdoptedActions) {
            var widget = this.widget;
            var form = actions.closest('form');
            var container = widget.dialog('actionsContainer');
            actions.find('[type=submit]').each(function(idx, btn) {
                $(btn).click(function() {
                    this.loadContent(form.attr('action'), form.attr('method'), form.serialize());
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

    _getActionsElement: function() {
        var el = this.options.actionsEl;
        if (typeof el == 'string') {
            el = this.dialogContent.find(el);
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

    loadContent: function(url, method, data) {
        if (typeof url == 'undefined' || !url) {
            url = window.location.href;
        }
        if (typeof method == 'undefined' || !method) {
            method = 'get';
        }
        var options = {
            url: url,
            type: method
        };
        if (typeof data != 'undefined') {
            options.data = data;
        }
        Backbone.$.ajax(options).done(function(content) {
            this.dialogContent = content;
            this.show();
        }.bind(this));
    },

    show: function() {
        if (!this.widget) {
            this.widget = this.dialogContent.dialog(this.options.dialogOptions);
        } else {
            this.widget.html(this.dialogContent);
        }

        if (this.options.isForm) {
            this.adoptActions();
        }
    }
});