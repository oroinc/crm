/*!
 * jQuery DialogExtend 1.0
 *
 * Copyright (c) 2010 Shum Ting Hin
 * 2012 Oro Inc
 *
 * Licensed under MIT
 *   http:// www.opensource.org/licenses/mit-license.php
 *
 * Project Home:
 *   http://code.google.com/p/jquery-dialogextend/
 *
 * Depends:
 *   jQuery 1.7.2
 *   jQuery UI Dialog 1.10.2
 *
 */
(function ($) {

    // default settings
    var defaults = {
        minimizeTo: false,
        maximizedHeightDecreaseBy: false,
        close: true,
        maximize: false,
        minimize: false,
        dblclick: false,
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
    };

    var settings = {};

    var methods = {

        init: function (options) {
            var self = this;
            // validation
            if (!$(self).dialog) {
                $.error("jQuery.dialogExtend Error : Only jQuery UI Dialog element is accepted");
            }
            // merge defaults & options, without modifying the defaults
            options = options || {};
            options.icons = options.icons || {};
            options.events = options.events || {};
            settings = $.extend({}, defaults, options);
            settings.icons = $.extend({}, defaults.icons, options.icons);
            settings.events = $.extend({}, defaults.events, options.events);
            settings.appendTo = $(self).parent().parent();
            // Fix parent position
            if (settings.appendTo.css('position') == 'static') {
                settings.appendTo.css('position', 'relative');
            }
            // initiate plugin...
            $(self).each(function () {
                $(this)
                    .dialogExtend("_verifySettings")
                    .dialogExtend("_initEvents")
                    .dialogExtend("_initButtons")
                    .dialogExtend("_initTitleBar")
                    // set default dialog state
                    .dialogExtend("_setState", "normal")
                    .dialogExtend("_initBottomLine")
                    .dialogExtend("_trigger", "load");
            });

            // Handle window resize
            var onResize = function() {
                if (self.dialogExtend("state") == "maximized") {
                    self.dialogExtend("_calculateNewMaximizedDimensions");
                }
            };
            $(window).resize(onResize);

            return self;
        },

        state: function () {
            return $(this).data("dialog-extend-state");
        },

        minimize: function () {
            var self = $(this);
            var widget = self.dialog('widget');

            self
                .dialogExtend("_trigger", "beforeMinimize")
                .dialogExtend("_saveSnapshot")
                .dialogExtend("_setState", "minimized")
                .dialogExtend("_toggleButtons")
                .dialogExtend("_trigger", "minimize");
            widget.hide();

            self.dialogExtend("_getMinimizeTo").show();

            // Make copy of widget to disable dialog events
            var minimizedEl = self.dialog("widget").clone();
            minimizedEl.find('.ui-dialog-content').remove();
            minimizedEl.find('.ui-resizable-handle').remove();
            // Add title attribute to be able to view full window title
            var title = minimizedEl.find('.ui-dialog-title');
            title.attr('title', title.text());
            minimizedEl.find('.ui-dialog-titlebar').dblclick(function() {
                minimizedEl.remove();
                widget.show();
                widget.find('.ui-dialog-titlebar').dblclick();
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
            minimizedEl.appendTo($(this).dialogExtend("_getMinimizeTo"));

            return self;
        },

        collapse: function () {
            var self = $(this);
            var newHeight = self.dialogExtend('_getTitleBarHeight');

            self
                .dialogExtend("_trigger", "beforeCollapse")
                .dialogExtend("_saveSnapshot")
                // modify dialog size (after hiding content)
                .dialog("option", {
                    resizable: false,
                    height: newHeight,
                    maxHeight: newHeight
                })
                // mark new state
                .dialogExtend("_setState", "collapsed")
                // trigger custom event
                .dialogExtend("_trigger", "collapse");
            
            return self;
        },

        maximize: function () {
            var self = $(this);
            if (self.dialogExtend('state') != 'normal') {
                // Normalize state
                self
                    .dialogExtend("_restoreWithoutTriggerEvent")
                    .dialogExtend("_setState", "normal");
            }
            self
                .dialogExtend("_trigger", "beforeMaximize")
                .each(function() {
                    if ($(this).dialogExtend("state") != "normal") {
                        $(this).dialogExtend("_restoreWithoutTriggerEvent");
                    }
                })
                .dialogExtend("_saveSnapshot")
                .dialogExtend("_calculateNewMaximizedDimensions")
                .dialogExtend("_setState", "maximized")
                .dialogExtend("_toggleButtons")
                .dialogExtend("_trigger", "maximize");

            return this;
        },

        restore: function () {
            var self = $(this);
            self
                .dialogExtend("_trigger", "beforeRestore")
                // restore to normal
                .dialogExtend("_restoreWithoutTriggerEvent")
                // mark new state ===> must set state *AFTER* restore because '_restoreWithoutTriggerEvent' will check 'beforeState'
                .dialogExtend("_setState", "normal")
                .dialogExtend("_toggleButtons")
                .dialogExtend("_trigger", "restore");

            return self;
        },

        _initBottomLine: function() {
            settings.bottomLine = $('#dialog-extend-parent-bottom');
            if (!settings.bottomLine.length) {
                settings.bottomLine = $('<div id="dialog-extend-parent-bottom"></div>');
                settings.bottomLine.css({
                    position: "fixed",
                    bottom: 0,
                    left: 0
                })
                .appendTo(document.body);
            }
            return this;
        },

        _initializeMinimizeContainer: function() {
            settings.minimizeTo = $('#dialog-extend-fixed-container');
            if (!settings.minimizeTo.length) {
                settings.minimizeTo = $('<div id="dialog-extend-fixed-container"></div>');
                settings.minimizeTo
                    .css({
                        position: "fixed",
                        bottom: 1,
                        left: settings.appendTo.offset().left,
                        zIndex: 9999
                    })
                    .hide()
                    .appendTo(settings.appendTo);
            }
        },

        _getMinimizeTo: function() {
            if (settings.minimizeTo === false) {
                this.dialogExtend("_initializeMinimizeContainer");
            }
            return $(settings.minimizeTo);
        },

        _calculateNewMaximizedDimensions: function() {
            var newHeight = $(this).dialogExtend("_getContainerHeight");
            var newWidth = settings.appendTo.width();
            var parentOffset = settings.appendTo.offset();
            $(this).dialog("option", {
                resizable: false,
                draggable : false,
                height: newHeight,
                width: newWidth,
                position: [parentOffset.left, parentOffset.top]
            });
            return this;
        },

        _getTitleBarHeight: function() {
            return $(this).dialog("widget").find(".ui-dialog-titlebar").height() + 15
        },

        _getContainerHeight: function() {
            var heightDelta = 0;
            if (settings.maximizedHeightDecreaseBy) {
                if ($.isNumeric(settings.maximizedHeightDecreaseBy)) {
                    heightDelta = settings.maximizedHeightDecreaseBy;
                } else if (settings.maximizedHeightDecreaseBy === 'minimize-bar') {
                    heightDelta = $(this).dialogExtend("_getMinimizeTo").height();
                } else {
                    heightDelta = $(settings.maximizedHeightDecreaseBy).height();
                }
            }

            return settings.bottomLine.offset().top - settings.appendTo.offset().top - heightDelta - 2;
        },

        _initButtons: function (el) {
            var self = $(this);
            if (typeof el == 'undefined') {
                el = self;
            }
            // start operation on titlebar
            var titlebar = el.dialog("widget").find(".ui-dialog-titlebar");
            // create container for buttons
            var buttonPane = $('<div class="ui-dialog-titlebar-buttonpane"></div>').appendTo(titlebar);
            $(buttonPane).css({
                "position": "absolute",
                "top": "50%",
                "right": "0.3em",
                "margin-top": "-10px",
                "height": "18px"
            });
            // move 'close' button to button-pane
            $(titlebar)
                .find(".ui-dialog-titlebar-close")
                // override some unwanted jquery-ui styles
                .css({ "position": "static", "top": "auto", "right": "auto", "margin": 0 })
                // change icon
                .find(".ui-icon").removeClass("ui-icon-closethick").addClass(settings.icons.close).end()
                // move to button-pane
                .appendTo(buttonPane)
                .end();
            // append other buttons to button-pane
            var types =  ['maximize', 'restore', 'minimize'];
            for (var key in types) if (typeof types[key] == 'string') {
                var type = types[key];
                if (typeof settings.icons[type] == 'string') {
                    buttonPane.append('<a class="ui-dialog-titlebar-' + type + ' ui-corner-all" href="#"><span class="ui-icon ' + settings.icons[type] + '">' + type + '</span></a>')
                } else {
                    settings.icons[type].addClass('ui-dialog-titlebar-' + type);
                    buttonPane.append(settings.icons[type]);
                }
            }
            buttonPane
                // add effect to buttons
                .find(".ui-dialog-titlebar-maximize,.ui-dialog-titlebar-minimize,.ui-dialog-titlebar-restore")
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
                })
                .end()
                // default show buttons
                // set button positions
                // on-click-button
                .find(".ui-dialog-titlebar-close")
                .toggle(settings.close)
                .end()
                .find(".ui-dialog-titlebar-maximize")
                .toggle(settings.maximize)
                .click(function (e) {
                    e.preventDefault();
                    $(self).dialogExtend("maximize");
                })
                .end()
                .find(".ui-dialog-titlebar-minimize")
                .toggle(settings.minimize)
                .click(function (e) {
                    e.preventDefault();
                    $(self).dialogExtend("minimize");
                })
                .end()
                .find(".ui-dialog-titlebar-restore")
                .hide()
                .click(function (e) {
                    e.preventDefault();
                    $(self).dialogExtend("restore");
                })
                .end();
            // other titlebar behaviors
            $(titlebar)
                // on-dblclick-titlebar : maximize/minimize/collapse/restore
                .dblclick(function (evt) {
                    if (settings.dblclick && settings.dblclick.length) {
                        $(self).dialogExtend($(self).dialogExtend("state") != "normal" ? "restore" : settings.dblclick);
                    }
                })
                // avoid text-highlight when double-click
                .select(function () {
                    return false;
                });
            
            return self;
        },

        _initEvents: function () {
            var self = this;
            // bind event callbacks which specified at init
            $.each(settings.events, function (type) {
                if ($.isFunction(settings.events[type])) {
                    $(self).bind(type + ".dialogExtend", settings.events[type]);
                }
            });
            
            return self;
        },

        _initTitleBar: function () {
            var self = this;
            // modify title bar
            switch (settings.titlebar) {
                case false:
                    // do nothing
                    break;
                case "transparent":
                    // remove title style
                    $(self)
                        .dialog("widget")
                        .find(".ui-dialog-titlebar")
                        .css({
                            "background-color": "transparent",
                            "background-image": "none",
                            "border": 0
                        });
                    break;
                default:
                    $.error("jQuery.dialogExtend Error : Invalid <titlebar> value '" + settings.titlebar + "'");
            }
            
            return self;
        },

        _loadSnapshot: function () {
            var self = this;
            return {
                "config": {
                    "resizable": $(self).data("original-config-resizable"),
                    "draggable": $(self).data("original-config-draggable")
                },
                "size": {
                    "height": $(self).data("original-size-height"),
                    "width": $(self).data("original-size-width"),
                    "maxHeight": $(self).data("original-size-maxHeight")
                },
                "position": {
                    "left": $(self).data("original-position-left"),
                    "top": $(self).data("original-position-top")
                }
            };
        },

        _restoreFromCollapsed: function () {
            var self = this;
            var original = $(this).dialogExtend("_loadSnapshot");
            // restore dialog
            $(self)
                // restore config & size
                .dialog("option", {
                    "resizable": original.config.resizable,
                    "height": original.size.height - self.dialogExtend('_getTitleBarHeight'),
                    "maxHeight": original.size.maxHeight
                });
            
            return self;
        },

        _restoreFromNormal: function () {
            // do nothing actually...
            return this;
        },

        _restoreFromMaximized: function () {
            var self = $(this);
            var original = $(this).dialogExtend("_loadSnapshot");
            // restore dialog
            self
                // restore config & size
                .dialog("option", {
                    resizable: original.config.resizable,
                    draggable: original.config.draggable,
                    height: original.size.height,
                    width: original.size.width,
                    maxHeight: original.size.maxHeight,
                    position: [ original.position.left, original.position.top ]
                })
            
            return self;
        },

        _restoreFromMinimized: function () {
            var self = $(this);
            var original = $(this).dialogExtend("_loadSnapshot");

            // restore position *AFTER* size restored
            self.dialog('widget').css({
                position: 'fixed',
                left: self.dialogExtend('_getVisibleLeft', original.position.left, original.size.width),
                top: self.dialogExtend('_getVisibleTop', original.position.top, original.size.height)
            });

            return self;
        },

        _getVisibleLeft: function(left, width) {
            var containerWidth = settings.appendTo.width();
            if (left + width > containerWidth) {
                return containerWidth - width;
            }
            return left;
        },

        _getVisibleTop: function(top, height) {
            var visibleTop = settings.bottomLine.offset().top
            if (top + height > visibleTop) {
                return visibleTop - height;
            }
            return top;
        },

        _restoreWithoutTriggerEvent: function () {
            var self = this;
            var beforeState = $(self).dialogExtend("state");
            $(self)
                // restore dialog according to previous state
                .dialogExtend(
                    beforeState == "maximized" ? "_restoreFromMaximized" :
                        beforeState == "minimized" ? "_restoreFromMinimized" :
                            beforeState == "collapsed" ? "_restoreFromCollapsed" :
                                beforeState == "normal" ? "_restoreFromNormal" :
                                    $.error("jQuery.dialogExtend Error : Cannot restore dialog from unknown state '" + beforeState + "'")
                );
            
            return self;
        },

        _saveSnapshot: function () {
            var self = this;
            // remember all configs under normal state
            if ($(self).dialogExtend("state") == "normal") {
                $(self)
                    .data("original-config-resizable", $(self).dialog("option", "resizable"))
                    .data("original-config-draggable", $(self).dialog("option", "draggable"))
                    .data("original-size-height", $(self).dialog("widget").height())
                    .data("original-size-width", $(self).dialog("option", "width"))
                    .data("original-size-maxHeight", $(self).dialog("option", "maxHeight"))
                    .data("original-position-left", $(self).dialog("widget").offset().left)
                    .data("original-position-top", $(self).dialog("widget").offset().top);
            }
            
            return self;
        },

        _setState: function (state) {
            var self = this;
            $(self)
                // toggle data state
                .data("dialog-extend-state", state)
                .dialog('widget')
                    .removeClass("ui-dialog-normal ui-dialog-maximized ui-dialog-minimized ui-dialog-collapsed")
                    .addClass("ui-dialog-" + state);

            return self;
        },

        _toggleButtons: function () {
            var self = this;
            // show or hide buttons & decide position
            $(self).dialog("widget")
                .find(".ui-dialog-titlebar-maximize")
                .toggle($(self).dialogExtend("state") != "maximized" && settings.maximize)
                .end()
                .find(".ui-dialog-titlebar-minimize")
                .toggle($(self).dialogExtend("state") != "minimized" && settings.minimize)
                .end()
                .find(".ui-dialog-titlebar-restore")
                .toggle($(self).dialogExtend("state") != "normal" && ( settings.maximize || settings.minimize ))
                .css({ "right": $(self).dialogExtend("state") == "maximized" ? "1.4em" : $(self).dialogExtend("state") == "minimized" ? !settings.maximize ? "1.4em" : "2.5em" : "-9999em" })
                .end();
            
            return self;
        },

        _trigger: function (type) {
            var self = this;
            // trigger event with namespace when user bind to it
            $(self).triggerHandler(type + ".dialogExtend", this);
            
            return self;
        },

        _verifySettings: function () {
            var checkOption = function(option, options) {
                if (settings[option] && options.indexOf(settings[option]) == -1) {
                    $.error("jQuery.dialogExtend Error : Invalid <" + option + "> value '" + settings[option] + "'");
                    settings[option] = false;
                }
            };

            checkOption('dblclick', ["maximize", "minimize", "collapse"]);
            checkOption('titlebar', ["transparent"]);
            
            return this;
        }

    };

    // core method
    $.fn.dialogExtend = function (method) {
        // method calling logic
        if (methods[ method ]) {
            return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === "object" || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error("jQuery.dialogExtend Error : Method <" + method + "> does not exist");
        }
    };

}(jQuery));