define(function(require) {
    'use strict';

    var $ = require('jquery');
    var __ = require('orotranslation/js/translator');
    var mediator = require('oroui/js/mediator');
    var messenger = require('oroui/js/messenger');
    var console = window.console;

    return function(options) {
        var currentState = options.currentState;
        var subscribedId = options.subscribedId;

        var handleButtonsState = function(state) {
            $('.subscription').css('display', 'none');

            if (state === subscribedId) {
                $('.unsubscribe-action').css('display', 'inline-block');
            } else {
                $('.subscribe-action').css('display', 'inline-block');
            }
        };

        handleButtonsState(currentState);

        $('.subscription').click(function(e) {
            e.preventDefault();

            var url = $(this).data('url');

            mediator.execute('showLoading');

            $.ajax(url, {
                success: function(response) {
                    handleButtonsState(response.state);

                    if (response.successful) {
                        messenger.notificationMessage('success', __('orocrm.magento.subscription.success'));
                        mediator.execute('refreshPage');
                    } else {
                        messenger.notificationMessage('error', __('orocrm.magento.subscription.error'));
                        if (console) {
                            console.warn(response.error);
                        }
                    }
                },
                error: function() {
                    messenger.notificationMessage('error', __('oro.integration.error'));
                },
                dataType: 'json'
            }).always(function() {
                mediator.execute('hideLoading');
            });
        });
    };
});
