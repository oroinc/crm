/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var $ = require('jquery');

    return function (options) {
        var $el = options._sourceElement,
            $enableEl = $el.find('#' + options.rfm_enable_id);

        var enableHandler = function() {
            if ($enableEl.is(':checked')) {
                $el.addClass('rfm_enabled');
                $el.find('.rfm_settings_row input:disabled').prop('disabled', false);
            } else {
                $el.removeClass('rfm_enabled');
                $el.find('.rfm_settings_row input').prop('disabled', true);
            }
        };

        $enableEl.on('click', enableHandler);
        $el.on('rfmSettingsRender', enableHandler);
    };
});
