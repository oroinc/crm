define(['jquery', 'underscore'], function($, _) {
    'use strict';

    function getField(name) {
        return $('[name*="[' + name + ']"]');
    }

    function toggleFieldValidationStatus(element, status) {
        if (_.isArray(element)) {
            _.each(element, function(e) {
                getField(e).toggleClass('ignored', !status).valid();
            });
        } else {
            getField(element).toggleClass('ignored', !status).valid();
        }
    }

    return [
        'Oro\\Bundle\\ContactUsBundle\\Validator\\ContactRequestCallbackValidator',
        function(value, element, params) {
            var targetValue = getField(params.target).val();

            _.each(params.deps, function(val, key) {
                toggleFieldValidationStatus(val, key === targetValue);
            });

            return true;
        }
    ];
});
