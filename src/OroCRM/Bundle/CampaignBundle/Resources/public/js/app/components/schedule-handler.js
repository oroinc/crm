/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var _ = require('underscore'),
        $ = require('jquery');

    return function (options) {
        var target = $(options.target),
            element = $(options.element),
            label = element.find('label'),
            hideOn = options.hideOn || [],
            showOn = options.showOn || [];

        target.on('change', function () {
            if (_.contains(hideOn, $(this).val())) {
                element
                    .addClass('hide')
                    .data('validation-ignore', true);

                if (label.hasClass('required')) {
                    label
                        .removeClass('required')
                        .find('em').remove();
                }
            }
            if (_.contains(showOn, $(this).val())) {
                element
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
