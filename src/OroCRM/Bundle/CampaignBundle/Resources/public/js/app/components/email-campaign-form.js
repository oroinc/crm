/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var _ = require('underscore'),
        $ = require('jquery'),
        mediator = require('oroui/js/mediator');

    return function (options) {
        var $schedule = $(options.scheduleEl),
            $scheduledFor = $(options.scheduledForEl),
            $transportEl = $(options.transportEl),
            label = $scheduledFor.find('label'),
            hideOn = options.hideOn || [],
            showOn = options.showOn || [];

        $transportEl.on('change', function() {
            mediator.execute('showLoading');

            var $form = $transportEl.closest('form'),
                data = $form.serializeArray(),
                url = $form.attr('action');
            data.push({name: 'formUpdateMarker', value: 1});

            $.post(url, data, function (res, status, jqXHR) {
                var formContent = $(res).find('#' + $form.prop('id'));
                if (formContent.length) {
                    $form.replaceWith(formContent);
                    formContent.validate({});
                    // update wdt
                    mediator.execute({name: 'updateDebugToolbar', silent: true}, jqXHR);
                    // process UI decorators
                    mediator.execute('layout:init', document.body);
                    mediator.execute('afterPageChange');
                }
            }).always(function () {
                mediator.execute('hideLoading');
            });
        });

        $schedule.on('change', function () {
            if (_.contains(hideOn, $(this).val())) {
                $scheduledFor
                    .addClass('hide')
                    .data('validation-ignore', true);

                if (label.hasClass('required')) {
                    label
                        .removeClass('required')
                        .find('em').remove();
                }
            }
            if (_.contains(showOn, $(this).val())) {
                $scheduledFor
                    .removeClass('hide')
                    .removeData('validation-ignore');

                if (!label.hasClass('required')) {
                    label
                        .addClass('required')
                        .append('<em>*</em>');
                }
            }
        });
    };
});
