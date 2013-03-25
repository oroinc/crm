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
    actions: null,

    // Windows manager global variables
    windowsPerRow: 10,
    windowOffsetX: 15,
    windowOffsetY: 15,
    windowX: 0,
    windowY: 0,
    defaultPos: 'center center',
    openedWindows: 0,

    /**
     * Initialize dialog
     */
    initialize: function() {
        if (this.options.isForm) {
            var runner = function(handlers) {
                return function() {
                    for (var i = 0; i < handlers.length; i++) if (_.isFunction(handlers[i])) {
                        handlers[i]();
                    }
                }
            };
            this.options.dialogOptions.close = runner([this.closeHandler.bind(this), this.options.dialogOptions.close]);
        }

        this.dialogContent = this.$el.clone(true);
    },

    /**
     * Move form actions to dialog
     */
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

    /**
     * Handle dialog close
     */
    closeHandler: function() {
        this.dialogContent.remove();
        this._getActionsElement().remove();
    },

    /**
     * Get form buttons
     *
     * @returns {(*|jQuery|HTMLElement)}
     * @private
     */
    _getActionsElement: function() {
        if (!this.actions) {
            this.actions = this.options.actionsEl;
            if (typeof this.actions == 'string') {
                this.actions = this.dialogContent.find(this.actions);
            }
        }
        return this.actions;
    },

    /**
     * Render dialog
     */
    render: function() {
        if (this.options.url !== false) {
            this.loadContent(this.options.url);
        } else {
            this.show();
        }
    },

    /**
     * Load dialog content
     *
     * @param {String} url
     * @param {String} method
     * @param {Object} data
     */
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

    /**
     * Show dialog
     */
    show: function() {
        if (!this.widget) {
            if (typeof this.options.dialogOptions.position == 'undefined') {
                this.options.dialogOptions.position = this._getWindowPlacement();
            }
            this.widget = this.dialogContent.dialog(this.options.dialogOptions);
        } else {
            this.widget.html(this.dialogContent);
        }

        if (this.options.isForm) {
            this.adoptActions();
        }
    },

    /**
     * Get next window position based
     *
     * @returns {{my: string, at: string, of: (*|jQuery|HTMLElement), within: (*|jQuery|HTMLElement)}}
     * @private
     */
    _getWindowPlacement: function() {
        var offset = 'center+' + Oro.windows.DialogView.prototype.windowX + ' center+' + Oro.windows.DialogView.prototype.windowY;

        Oro.windows.DialogView.prototype.openedWindows++;
        if (Oro.windows.DialogView.prototype.openedWindows % Oro.windows.DialogView.prototype.windowsPerRow === 0) {
            var rowNum = Oro.windows.DialogView.prototype.openedWindows / Oro.windows.DialogView.prototype.windowsPerRow;
            Oro.windows.DialogView.prototype.windowX = rowNum * Oro.windows.DialogView.prototype.windowsPerRow * Oro.windows.DialogView.prototype.windowOffsetX;
            Oro.windows.DialogView.prototype.windowY = 0;

        } else {
            Oro.windows.DialogView.prototype.windowX += Oro.windows.DialogView.prototype.windowOffsetX;
            Oro.windows.DialogView.prototype.windowY += Oro.windows.DialogView.prototype.windowOffsetY;
        }

        var position = {
            my: offset,
            at: Oro.windows.DialogView.prototype.defaultPos,
            of: $('body')
        };
        if (typeof this.options.dialogOptions.appendTo != 'undefined') {
            position.within = this.options.dialogOptions.appendTo;
        }
        return position;
    }
});
