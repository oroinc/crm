/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var _ = require('underscore'),
        $ = require('jquery'),
        mediator = require('oroui/js/mediator');

    return function (options) {
        var $schedule = options._sourceElement.find(options.scheduleEl),
            $scheduledFor = options._sourceElement.find(options.scheduledForEl),
            $transportEl = options._sourceElement.find(options.transportEl),
            $label = $scheduledFor.find('label'),
            hideOn = options.hideOn || [],
            showOn = options.showOn || [];

        $transportEl.on('change', function() {
            mediator.execute('showLoading');

            var $form = $transportEl.closest('form'),
                data = $form.serializeArray(),
                url = $form.attr('action');
            data.push({name: 'formUpdateMarker', value: 1});

            var event = {formEl: $form, data: data, reloadManually: true};
            mediator.trigger('integrationFormReload:before', event);

            if (event.reloadManually) {
                mediator.execute('submitPage', {url: url, type: $form.attr('method'), data: $.param(data)});
            }
        });

        $schedule.on('change', function () {
            if (_.contains(hideOn, $(this).val())) {
                $scheduledFor
                    .addClass('hide')
                    .data('validation-ignore', true);

                if ($label.hasClass('required')) {
                    $label
                        .removeClass('required')
                        .find('em').remove();
                }
            }
            if (_.contains(showOn, $(this).val())) {
                $scheduledFor
                    .removeClass('hide')
                    .removeData('validation-ignore');

                if (!$label.hasClass('required')) {
                    $label
                        .addClass('required')
                        .append('<em>*</em>');
                }
            }
        });
    };
});
