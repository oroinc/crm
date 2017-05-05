define(['jquery', 'underscore'], function($, _) {
    'use strict';

    var NotBlank = {
        'message': 'This value should not be blank.',
        'payload': null
    };

    var getField = function(name) {
        return $('[name*="[' + name + ']"]');
    };

    var resetFieldStatus = function(allFields, requiredFields, silent) {
        var isArray = _.isArray(requiredFields);

        _.each(allFields, function(field) {
            var $field = getField(field);

            var validationData = $field.data('validation') || {};
            var isRequired = isArray ? _.indexOf(requiredFields, field) !== -1 : field === requiredFields;
            if (isRequired) {
                validationData.NotBlank = NotBlank;
            } else {
                delete validationData.NotBlank;
            }

            $field.data('validation', validationData);

            if (!silent) {
                $field.valid();
            }
        });
    };

    var resolveFields = function(list) {
        return _.uniq(_.flatten(_.values(list)));
    };

    var validate = function($field, params, silent) {
        var requiredFields = params.deps[$field.val()];
        var allFields = resolveFields(params.deps);

        resetFieldStatus(allFields, requiredFields, silent);
    };

    return [
        'Oro\\Bundle\\ContactUsBundle\\Validator\\ContactRequestCallbackValidator',
        function(value, element, params) {
            var event = 'change.ContactRequestCallbackValidator';
            var $field = getField(params.target);
            $field.off(event).on(event, function() {
                validate($field, params);
            });
            validate($field, params, true);

            return true;
        }
    ];
});
