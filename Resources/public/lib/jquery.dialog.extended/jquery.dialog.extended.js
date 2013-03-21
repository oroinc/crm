/*!
 * jQuery Extended Dialog 2.0
 *
 * Copyright (c) 2013 Oro Inc
 * Inspired by DialogExtend Copyright (c) 2010 Shum Ting Hin http://code.google.com/p/jquery-dialogextend/
 *
 * Licensed under MIT
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Depends:
 *   jQuery 1.7.2
 *   jQuery UI Dialog 1.10.2
 *
 */
(function ($) {
$.widget( "ui.dialog", $.ui.dialog, {
    version: "2.0.0",

    options: $.extend($.ui.dialog.options, {
        minimizeTo: false,
        maximizedHeightDecreaseBy: false,
        allowClose: true,
        allowMaximize: true,
        allowMinimize: true,
        dblclick: "maximize",
        titlebar: false,
        icons: {
            close: "ui-icon-closethick",
            maximize: "ui-icon-extlink",
            minimize: "ui-icon-minus",
            restore: "ui-icon-newwin"
        },
        events: {
            load: null,
            beforeCollapse: null,
            beforeMaximize: null,
            beforeMinimize: null,
            beforeRestore: null,
            collapse: null,
            maximize: null,
            minimize: null,
            restore: null
        }
    }),

    _create: function () {
        this._super();
        this._setState("normal");

        // Fix parent position
        var appendTo = this._appendTo();
        if (appendTo.css('position') == 'static') {
            appendTo.css('position', 'relative');
        }
        // initiate plugin...
        this._verifySettings();
        this._initEvents();
        // set default dialog state
        this._initBottomLine();
        this._trigger("load");

        // Handle window resize
        var self = this;
        var onResize = function() {
            if (self.state() == "maximized") {
                self._calculateNewMaximizedDimensions();
            }
        };
        $(window).resize(onResize);
    },

    state: function () {
        return this._state;
    },

    minimize: function () {
        var widget = this.widget();

        this._trigger("beforeMinimize");
        this._saveSnapshot();
        this._setState("minimized");
        this._toggleButtons();
        this._trigger("minimize");
        widget.hide();

        this._getMinimizeTo().show();

        // Make copy of widget to disable dialog events
        var minimizedEl = widget.clone();
        minimizedEl.css({'height': 'auto'});
        minimizedEl.find('.ui-dialog-content').remove();
        minimizedEl.find('.ui-resizable-handle').remove();
        // Add title attribute to be able to view full window title
        var title = minimizedEl.find('.ui-dialog-title');
        title.disableSelection().attr('title', title.text());
        var self = this;
        minimizedEl.find('.ui-dialog-titlebar').dblclick(function() {
            minimizedEl.remove();
            widget.show();
            self.uiDialogTitlebar.dblclick();
        });
        // Proxy events to original window
        var buttons = ['close', 'maximize', 'restore'];
        for (var i = 0; i < buttons.length; i++) {
            var btnClass = '.ui-dialog-titlebar-' + buttons[i];
            minimizedEl.find(btnClass).click(
                function(btnClass) {
                    return function() {
                        minimizedEl.remove();
                        widget.show();
                        widget.find(btnClass).click();
                    }
                }(btnClass));
        }
        minimizedEl.show();
        minimizedEl.appendTo(this._getMinimizeTo());

        return this;
    },

    collapse: function () {
        var newHeight = this._getTitleBarHeight();

        this._trigger("beforeCollapse");
        this._saveSnapshot();
        // modify dialog size (after hiding content)
        this._setOptions({
            resizable: false,
            height: newHeight,
            maxHeight: newHeight
        });
        // mark new state
        this._setState("collapsed");
        // trigger custom event
        this._trigger("collapse");

        return this;
    },

    maximize: function () {
        if (this.state() != 'normal') {
            // Normalize state
            this._restoreWithoutTriggerEvent();
            this._setState("normal");
        }
        this._trigger("beforeMaximize");

        if (this.state() != "normal") {
            this._restoreWithoutTriggerEvent();
        }

        this._saveSnapshot();
        this._calculateNewMaximizedDimensions();
        this._setState("maximized");
        this._toggleButtons();
        this._trigger("maximize");

        return this;
    },

    restore: function () {
        this._trigger("beforeRestore");
        // restore to normal
        this._restoreWithoutTriggerEvent();
        this._setState("normal");
        this._toggleButtons();
        this._trigger("restore");

        return this;
    },

    _initBottomLine: function() {
        this.bottomLine = $('#dialog-extend-parent-bottom');
        if (!this.bottomLine.length) {
            this.bottomLine = $('<div id="dialog-extend-parent-bottom"></div>');
            this.bottomLine.css({
                position: "fixed",
                bottom: 0,
                left: 0
            })
            .appendTo(document.body);
        }
        return this;
    },

    _initializeMinimizeContainer: function() {
        this.options.minimizeTo = $('#dialog-extend-fixed-container');
        if (!this.options.minimizeTo.length) {
            this.options.minimizeTo = $('<div id="dialog-extend-fixed-container"></div>');
            this.options.minimizeTo
                .css({
                    position: "fixed",
                    bottom: 1,
                    left: this._appendTo().offset().left,
                    zIndex: 9999
                })
                .hide()
                .appendTo(this._appendTo());
        }
    },

    _getMinimizeTo: function() {
        if (this.options.minimizeTo === false) {
            this._initializeMinimizeContainer();
        }
        return $(this.options.minimizeTo);
    },

    _calculateNewMaximizedDimensions: function() {
        var newHeight = this._getContainerHeight();
        var newWidth = this._appendTo().width();
        var parentOffset = this._appendTo().offset();
        this._setOptions({
            resizable: false,
            draggable : false,
            height: newHeight,
            width: newWidth,
            position: [parentOffset.left, parentOffset.top]
        });
        return this;
    },

    _getTitleBarHeight: function() {
        return this.uiDialogTitlebar.height() + 15
    },

    _getContainerHeight: function() {
        var heightDelta = 0;
        if (this.options.maximizedHeightDecreaseBy) {
            if ($.isNumeric(this.options.maximizedHeightDecreaseBy)) {
                heightDelta = this.options.maximizedHeightDecreaseBy;
            } else if (this.options.maximizedHeightDecreaseBy === 'minimize-bar') {
                heightDelta = this._getMinimizeTo().height();
            } else {
                heightDelta = $(this.maximizedHeightDecreaseBy).height();
            }
        }

        return this.bottomLine.offset().top - this._appendTo().offset().top - heightDelta - 2;
    },

    _createButtons: function() {
        this._super();
        this._initButtons();
    },

    _initButtons: function (el) {
        var self = this;
        if (typeof el == 'undefined') {
            el = this;
        }
        // start operation on titlebar
        // create container for buttons
        var buttonPane = $('<div class="ui-dialog-titlebar-buttonpane"></div>').appendTo(this.uiDialogTitlebar);
        // move 'close' button to button-pane
        this._buttons = {};
        this.uiDialogTitlebarClose
            // override some unwanted jquery-ui styles
            .css({ "position": "static", "top": "auto", "right": "auto", "margin": 0 })
            // change icon
            .find(".ui-icon").removeClass("ui-icon-closethick").addClass(this.options.icons.close).end()
            // move to button-pane
            .appendTo(buttonPane)
            .end();
        // append other buttons to button-pane
        var types =  ['maximize', 'restore', 'minimize'];
        for (var key in types) if (typeof types[key] == 'string') {
            var type = types[key];
            var button = this.options.icons[type];
            if (typeof this.options.icons[type] == 'string') {
                button = '<a class="ui-dialog-titlebar-' + type + ' ui-corner-all" href="#"><span class="ui-icon ' + this.options.icons[type] + '">' + type + '</span></a>';

            } else {
                button.addClass('ui-dialog-titlebar-' + type);
            }
            button = $(button);
            button
                .attr("role", "button")
                .mouseover(function () {
                    $(this).addClass("ui-state-hover");
                })
                .mouseout(function () {
                    $(this).removeClass("ui-state-hover");
                })
                .focus(function () {
                    $(this).addClass("ui-state-focus");
                })
                .blur(function () {
                    $(this).removeClass("ui-state-focus");
                });
            this._buttons[type] = button;
            buttonPane.append(button);
        }

        this.uiDialogTitlebarClose.toggle(this.options.allowClose);

        this._buttons['maximize']
            .toggle(this.options.allowMaximize)
            .click(function (e) {
                e.preventDefault();
                self.maximize();
            });

        this._buttons['minimize']
            .toggle(this.options.allowMinimize)
            .click(function (e) {
                e.preventDefault();
                self.minimize();
            });

        this._buttons['restore']
            .hide()
            .click(function (e) {
                e.preventDefault();
                self.restore();
            });

        // other titlebar behaviors
        this.uiDialogTitlebar
            // on-dblclick-titlebar : maximize/minimize/collapse/restore
            .dblclick(function (evt) {
                if (self.options.dblclick && self.options.dblclick.length) {
                    if (self.state() != 'normal') {
                        self.restore();
                    } else {
                        self[self.options.dblclick]();
                    }
                }
            })
            // avoid text-highlight when double-click
            .select(function () {
                return false;
            });

        return this;
    },

    _initEvents: function () {
        var self = this;
        // bind event callbacks which specified at init
        $.each(this.options.events, function (type) {
            if ($.isFunction(self.options.events[type])) {
                self.bind(type, self.options.events[type]);
            }
        });

        return this;
    },

    _createTitlebar: function () {
        this._super();
        this.uiDialogTitlebar.disableSelection();

        // modify title bar
        switch (this.options.titlebar) {
            case false:
                // do nothing
                break;
            case "transparent":
                // remove title style
                this.uiDialogTitlebar
                    .css({
                        "background-color": "transparent",
                        "background-image": "none",
                        "border": 0
                    });
                break;
            default:
                $.error("jQuery.dialogExtend Error : Invalid <titlebar> value '" + this.options.titlebar + "'");
        }

        return this;
    },

    _restoreFromCollapsed: function () {
        var original = this._loadSnapshot();
        // restore dialog
        this._setOptions({
                "resizable": original.config.resizable,
                "height": original.size.height - this._getTitleBarHeight(),
                "maxHeight": original.size.maxHeight
            });

        return this;
    },

    _restoreFromMaximized: function () {
        var original = this._loadSnapshot();
        // restore dialog
        this._setOptions({
            resizable: original.config.resizable,
            draggable: original.config.draggable,
            height: original.size.height,
            width: original.size.width,
            maxHeight: original.size.maxHeight,
            position: [ original.position.left, original.position.top ]
        });

        return this;
    },

    _restoreFromMinimized: function () {
        var original = this._loadSnapshot();

        this._setOptions({
            resizable: original.config.resizable,
            draggable: original.config.draggable,
            height: original.size.height - this._getTitleBarHeight() - 3,
            width: original.size.width,
            maxHeight: original.size.maxHeight
        });

        // restore position *AFTER* size restored
        this.widget().css({
            position: 'fixed',
            left: this._getVisibleLeft(original.position.left, original.size.width),
            top: this._getVisibleTop(original.position.top, original.size.height)
        });

        return this;
    },

    _getVisibleLeft: function(left, width) {
        var containerWidth = this._appendTo().width();
        if (left + width > containerWidth) {
            return containerWidth - width;
        }
        return left;
    },

    _getVisibleTop: function(top, height) {
        var visibleTop = this.bottomLine.offset().top;
        if (top + height > visibleTop) {
            return visibleTop - height;
        }
        return top;
    },

    _restoreWithoutTriggerEvent: function () {
        var beforeState = this.state();
        var method = '_restoreFrom' + beforeState.charAt(0).toUpperCase() + beforeState.slice(1);
        if ($.isFunction(this[method])) {
            this[method]();
        } else {
            $.error("jQuery.dialogExtend Error : Cannot restore dialog from unknown state '" + beforeState + "'")
        }

        return this;
    },

    _saveSnapshot: function () {
        // remember all configs under normal state
        if (this.state() == "normal") {
            this._snapshot = {
                "config": {
                    "resizable": this.options.resizable,
                    "draggable": this.options.draggable
                },
                "size": {
                    "height": this.widget().height(),
                    "width": this.options.width,
                    "maxHeight": this.options.maxHeight
                },
                "position": this.widget().offset()
            };
        }

        return this;
    },

    _loadSnapshot: function() {
        return this._snapshot;
    },

    _setState: function (state) {
        this._state = state;
            // toggle data state
        this.widget()
            .removeClass("ui-dialog-normal ui-dialog-maximized ui-dialog-minimized ui-dialog-collapsed")
            .addClass("ui-dialog-" + state);

        return this;
    },

    _toggleButtons: function () {
        // show or hide buttons & decide position
        this._buttons['maximize']
            .toggle(this.state() != "maximized" && this.options.allowMaximize);

        this._buttons['minimize']
            .toggle(this.state() != "minimized" && this.options.allowMinimize);

        this._buttons['restore']
            .toggle(this.state() != "normal" && ( this.options.allowMaximize || this.options.allowMinimize ))
            .css({ "right": this.state() == "maximized" ? "1.4em" : this.state() == "minimized" ? !this.options.allowMaximize ? "1.4em" : "2.5em" : "-9999em" });

        return this;
    },

    _verifySettings: function () {
        var self = this;
        var checkOption = function(option, options) {
            if (self.options[option] && options.indexOf(self.options[option]) == -1) {
                $.error("jQuery.dialogExtend Error : Invalid <" + option + "> value '" + self.options[option] + "'");
                self.options[option] = false;
            }
        };

        checkOption('dblclick', ["maximize", "minimize", "collapse"]);
        checkOption('titlebar', ["transparent"]);

        return this;
    }
});

}( jQuery ) );