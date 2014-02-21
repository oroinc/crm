// Open dialog to log a call
require(
    ['jquery', 'underscore', 'oro/dialog-widget', 'oro/widget-manager'],
    function ($, _, DialogWidget, WidgetManager) {
        'use strict';
        $(function () {
            $(document).on('click', '.log-call-button', function (e) {
                var element = $(this);
                var url = element.data('url');
                if (_.isUndefined(url)) {
                    url = element.attr('href');
                }

                // only one instance of widget is allowed
                if (element.data('widget-opened')) {
                    return;
                } else {
                    element.data('widget-opened', true);
                }

                // create and open widget
                var widget = new DialogWidget({
                    url: url,
                    dialogOptions: {
                        allowMaximize: true,
                        allowMinimize: true,
                        dblclick: 'maximize',
                        maximizedHeightDecreaseBy: 'minimize-bar',
                        width: 1000,
                        title: element.attr('title')
                    }
                });

                // reload widget with list of contact calls
                widget.on('contactCallLogged', function () {
                    WidgetManager.getWidgetInstanceByAlias('contact_calls', function (contactCallsWidget) {
                        contactCallsWidget.loadContent();
                    });
                });

                widget.on('widgetRemove', function () {
                    element.data('widget-opened', false);
                });

                widget.render();

                e.preventDefault();
            });
        });
    }
);
