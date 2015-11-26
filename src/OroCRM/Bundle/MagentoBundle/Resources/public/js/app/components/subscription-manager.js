define(function(require) {
    'use strict';

    var $ = require('jquery');
    var __ = require('orotranslation/js/translator');
    var mediator = require('oroui/js/mediator');
    var messenger = require('oroui/js/messenger');
    var console = window.console;

    return function(options) {
        options._sourceElement.find('.subscription').click(function(e) {
            e.preventDefault();

            var url = $(this).data('url');

            mediator.execute('showLoading');

            $.ajax(url, {
                success: function(response) {
                    if (response.successful) {
                        mediator.once('page:afterChange', function() {
                            messenger.notificationMessage('success', __('orocrm.magento.subscription.success'));
                        });

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
