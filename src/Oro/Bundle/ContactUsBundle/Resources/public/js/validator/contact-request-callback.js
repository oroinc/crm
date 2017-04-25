define(['jquery', 'underscore'], function($, _) {
    'use strict';

    var defaultValidations = {
        NotBlank: {
            'message': 'This value should not be blank.',
            'payload': null
        }
    };

    function getField(name) {
        return $('[name*="[' + name + ']"]');
    }

    function resetFieldStatus(fields) {
        _.each(fields, function(field) {
            var validationData = getField(field).data('validation');
            getField(field).data('validation', _.extend({}, validationData, defaultValidations));
            getField(field).addClass('ignored').valid();
        });
    }

    function toggleFieldValidationStatus(element) {
        if (_.isArray(element)) {
            _.each(element, function(e) {
                getField(e).removeClass('ignored').valid();
            });
        } else {
            getField(element).removeClass('ignored').valid();
        }
    }

    function resolveFields(list) {
        return _.uniq(_.flatten(_.values(list)));
    }

    return [
        'Oro\\Bundle\\ContactUsBundle\\Validator\\ContactRequestCallbackValidator',
        function(value, element, params) {
            var targetValue = getField(params.target).val();

            _.each(params.deps, function(val, key, list) {
                if (key === targetValue) {
                    resetFieldStatus(resolveFields(list), key);
                    toggleFieldValidationStatus(val);
                }
            });

            return true;
        }
    ];
});
