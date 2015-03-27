/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    return function (options) {
        var $ = require('jquery'),
            __ = require('orotranslation/js/translator'),
            mediator = require('oroui/js/mediator'),
            messenger = require('oroui/js/messenger'),
            currentState = options.currentState,
            subscribedId = options.subscribedId;

        var handleButtonsState = function (state) {
            $('.subscription').css('display', 'none');

            if (state == subscribedId) {
                $('.unsubscribe-action').css('display', 'inline-block');
            } else {
                $('.subscribe-action').css('display', 'inline-block');
            }
        };

        handleButtonsState(currentState);

        $('.subscription').click(function (e) {
            e.preventDefault();

            var url = $(this).data('url');

            mediator.execute('showLoading');

            $.ajax(url, {
                success: function (response) {
                    handleButtonsState(response.state);

                    if (response.successful) {
                        messenger.notificationMessage('success', __('orocrm.magento.subscription.success'));
                        mediator.execute('refreshPage');
                    } else {
                        messenger.notificationMessage('error', __('orocrm.magento.subscription.error'));
                        console.warn(response.error);
                    }
                },
                error: function () {
                    messenger.notificationMessage('error', __('oro.integration.error'));
                },
                dataType: 'json'
            }).always(function () {
                mediator.execute('hideLoading');
            });
        });
    };
});
