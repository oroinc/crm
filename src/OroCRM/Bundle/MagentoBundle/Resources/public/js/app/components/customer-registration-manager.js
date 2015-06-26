/*jslint nomen: true*/
/*global define*/
define(function(require) {
    'use strict';

    return function(options) {
        var $ = require('jquery'),
            __ = require('orotranslation/js/translator'),
            mediator = require('oroui/js/mediator'),
            messenger = require('oroui/js/messenger');

        $('.customer-registration').click(function(e) {
            e.preventDefault();

            var url = $(this).data('url');

            mediator.execute('showLoading');

            $.ajax(url, {
                method: 'POST',
                success: function(response) {
                    if (response.successful) {
                        messenger.notificationMessage('success', __('orocrm.magento.customer_registration.success'));
                        mediator.execute('refreshPage');
                    } else {
                        messenger.notificationMessage('error', __('orocrm.magento.customer_registration.error'));
                        console.warn(response.error);
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
