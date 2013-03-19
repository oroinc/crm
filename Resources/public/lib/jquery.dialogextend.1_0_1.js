/*!
 * jQuery DialogExtend 1.0
 *
 * Copyright (c) 2010 Shum Ting Hin
 *
 * Licensed under MIT
 *   http:// www.opensource.org/licenses/mit-license.php
 *
 * Project Home:
 *   http:// code.google.com/p/jquery-dialogextend/
 *
 * Depends:
 *   jQuery 1.7.2
 *   jQuery UI Dialog 1.10.2
 *
 */
(function ($) {

    // default settings
    var defaults = {
        minimizedWidth: 200,
        minimizeTo: false,
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

    var settings;

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
                    // do bunch of things...
                    .dialogExtend("_verifySettings")
                    .dialogExtend("_initEvents")
                    .dialogExtend("_initStyles")
                    .dialogExtend("_initButtons")
                    .dialogExtend("_initTitleBar")
                    // set default dialog state
                    .dialogExtend("_setState", "normal")
                    // trigger custom event when done
                    .dialogExtend("_trigger", "load");
            });

            // Handle window resize
            var onResize = function() {
                if (self.dialogExtend("state") == "maximized") {
                    self.dialogExtend("_calculateNewMaximizedDimensions");
                }
            };
            $(window).resize(onResize);

            if (!settings.minimizeTo) {
                self.dialogExtend("_initializeMinimizeContainer");
            }

            return self;
        },

        state: function () {
            return $(this).data("dialog-extend-state");
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

        _initializeMinimizeContainer: function() {
            settings.minimizeTo = $('<div id="dialog-extend-fixed-container"></div>')
                .css({
                    position: "fixed",
                    bottom: 1,
                    left: settings.appendTo.offset().left,
                    zIndex: 9999
                })
                .hide()
                .appendTo(document.body);
        },

        _calculateNewMaximizedDimensions: function() {
            var newHeight = settings.appendTo.height();
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

        minimize: function () {
            var self = $(this);
            var newHeight = self.dialogExtend('_getTitleBarHeight');
            var newWidth = settings.minimizedWidth;

            var widget = self.dialog("widget");
            self
                .dialogExtend("_trigger", "beforeMinimize")
                .dialogExtend("_saveSnapshot")
                .dialog("option", {
                    resizable: false,
                    draggable: false,
                    height: newHeight,
                    width: newWidth
                })
                // mark new state
                .dialogExtend("_setState", "minimized")
                // modify dialog button according to new state
                .dialogExtend("_toggleButtons")
                // trigger custom event
                .dialogExtend("_trigger", "minimize");

            // avoid title text overlap buttons
            widget
                .find(".ui-dialog-titlebar").each(function () {
                    var titlebar = this;
                    var buttonPane = $(this).find(".ui-dialog-titlebar-buttonpane");
                    var titleText = $(this).find(".ui-dialog-title");
                    $(titleText).css({
                        overflow: 'hidden',
                        width: $(titlebar).width() - $(buttonPane).width() + 10
                    });
                });

            settings.minimizeTo.show();
            widget.appendTo(settings.minimizeTo);

            return self;
        },

        _getTitleBarHeight: function() {
            return $(this).dialog("widget").find(".ui-dialog-titlebar").height() + 15
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

        _initButtons: function () {
            var self = this;
            // start operation on titlebar
            var titlebar = $(self).dialog("widget").find(".ui-dialog-titlebar");
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

        _initStyles: function () {
            var self = this;
            // append styles for this plugin to body
            if (!$(".dialog-extend-css").length) {
                var style = '';
                style += '<style class="dialog-extend-css" type="text/css">';
                style += '.ui-dialog .ui-dialog-titlebar-buttonpane>a { float: right; }';
                style += '.ui-dialog .ui-dialog-titlebar-maximize,';
                style += '.ui-dialog .ui-dialog-titlebar-minimize,';
                style += '.ui-dialog .ui-dialog-titlebar-restore { width: 19px; padding: 1px; height: 18px; }';
                style += '.ui-dialog .ui-dialog-titlebar-maximize span,';
                style += '.ui-dialog .ui-dialog-titlebar-minimize span,';
                style += '.ui-dialog .ui-dialog-titlebar-restore span { display: block; margin: 1px; }';
                style += '.ui-dialog .ui-dialog-titlebar-maximize:hover,';
                style += '.ui-dialog .ui-dialog-titlebar-maximize:focus,';
                style += '.ui-dialog .ui-dialog-titlebar-minimize:hover,';
                style += '.ui-dialog .ui-dialog-titlebar-minimize:focus,';
                style += '.ui-dialog .ui-dialog-titlebar-restore:hover,';
                style += '.ui-dialog .ui-dialog-titlebar-restore:focus { padding: 0; }';
                style += '.ui-dialog .ui-dialog-titlebar ::selection { background-color: transparent; }';
                style += '</style>';
                $(style).appendTo("body");
            }
            
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
            // restore dialog
            self.dialog("widget").appendTo(settings.appendTo);
                // restore config & size
            self.dialog("option", {
                    resizable: original.config.resizable,
                    draggable: original.config.draggable,
                    height: original.size.height - self.dialogExtend('_getTitleBarHeight') - 3,
                    width: original.size.width,
                    maxHeight: original.size.maxHeight
                })
            // restore position *AFTER* size restored
            self.dialog('widget').css({
                position: 'fixed',
                left: original.position.left,
                top: original.position.top
            });

            return self;
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
            var self = this;
            // check <dblclick> option
            if (!settings.dblclick) {
            } else if (settings.dblclick == "maximize") {
            } else if (settings.dblclick == "minimize") {
            } else if (settings.dblclick == "collapse") {
            } else {
                $.error("jQuery.dialogExtend Error : Invalid <dblclick> value '" + settings.dblclick + "'");
                settings.dblclick = false;
            }
            // check <titlebar> option
            if (!settings.titlebar) {
            } else if (settings.titlebar == "none") {
            } else if (settings.titlebar == "transparent") {
            } else {
                $.error("jQuery.dialogExtend Error : Invalid <titlebar> value '" + settings.titlebar + "'");
                settings.titlebar = false;
            }
            
            return self;
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