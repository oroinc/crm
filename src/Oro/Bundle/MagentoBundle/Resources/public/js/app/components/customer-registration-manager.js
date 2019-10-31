define(function(require) {
    'use strict';

    return function(options) {
        const $ = require('jquery');
        const __ = require('orotranslation/js/translator');
        const mediator = require('oroui/js/mediator');
        const messenger = require('oroui/js/messenger');

        $('.customer-registration').click(function(e) {
            e.preventDefault();

            const url = $(this).data('url');

            mediator.execute('showLoading');

            $.ajax(url, {
                method: 'POST',
                success: function(response) {
                    if (response.successful) {
                        messenger.notificationMessage('success', __('oro.magento.customer_registration.success'));
                        mediator.execute('refreshPage');
                    } else {
                        messenger.notificationMessage('error', __('oro.magento.customer_registration.error'));
                        console.warn(response.error);
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
