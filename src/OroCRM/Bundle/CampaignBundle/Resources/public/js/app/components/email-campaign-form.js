define(function(require) {
    'use strict';

    var _ = require('underscore');
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');

    return function(options) {
        var $schedule = options._sourceElement.find(options.scheduleEl);
        var $scheduledFor = options._sourceElement.find(options.scheduledForEl);
        var $transportEl = options._sourceElement.find(options.transportEl);
        var $label = $scheduledFor.find('label');
        var hideOn = options.hideOn || [];
        var showOn = options.showOn || [];

        $transportEl.on('change', function() {
            mediator.execute('showLoading');

            var $form = $transportEl.closest('form');
            var data = $form.serializeArray();
            var url = $form.attr('action');
            data.push({name: 'formUpdateMarker', value: 1});

            var event = {formEl: $form, data: data, reloadManually: true};
            mediator.trigger('integrationFormReload:before', event);

            if (event.reloadManually) {
                mediator.execute('submitPage', {url: url, type: $form.attr('method'), data: $.param(data)});
            }
        });

        $schedule.on('change', function() {
            if (_.contains(hideOn, $(this).val())) {
                $scheduledFor.addClass('hide');
                $scheduledFor.find('input').each(function() {
                    $(this).rules('remove', 'NotBlank');
                });

                if ($label.hasClass('required')) {
                    $label
                        .removeClass('required')
                        .find('em').html('&nbsp;');
                }
            }
            if (_.contains(showOn, $(this).val())) {
                $scheduledFor.removeClass('hide');

                $scheduledFor.find('input').each(function() {
                    $(this)
                        .removeClass('hide')
                        .rules('add', 'NotBlank');
                });

                if (!$label.hasClass('required')) {
                    $label
                        .addClass('required')
                        .find('em').html('*');
                }
            }
        });
    };
});
