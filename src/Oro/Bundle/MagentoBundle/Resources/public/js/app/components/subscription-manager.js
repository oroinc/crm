define(function(require) {
    'use strict';

    const $ = require('jquery');
    const __ = require('orotranslation/js/translator');
    const mediator = require('oroui/js/mediator');
    const messenger = require('oroui/js/messenger');
    const console = window.console;

    return function(options) {
        options._sourceElement.find('.subscription').click(function(e) {
            e.preventDefault();

            const url = $(this).data('url');

            mediator.execute('showLoading');

            $.ajax(url, {
                method: 'POST',
                success: function(response) {
                    if (response.successful) {
                        mediator.once('page:afterChange', function() {
                            messenger.notificationMessage('success', __('oro.magento.subscription.success'));
                        });

                        mediator.execute('refreshPage');
                    } else {
                        messenger.notificationMessage('error', __('oro.magento.subscription.error'));
                        if (console) {
                            console.warn(response.error);
                        }
                    }
                },
                errorHandlerMessage: __('oro.integration.error'),
                dataType: 'json'
            }).always(function() {
                mediator.execute('hideLoading');
            });
        });
    };
});
