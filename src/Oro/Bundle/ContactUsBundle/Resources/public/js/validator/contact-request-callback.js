define(['jquery', 'underscore'], function($, _) {
    'use strict';

    const NotBlank = {
        message: 'This value should not be blank.',
        payload: null
    };

    const getField = function(name) {
        return $('[name*="[' + name + ']"]');
    };

    const resetFieldStatus = function(allFields, requiredFields, silent) {
        const isArray = _.isArray(requiredFields);

        _.each(allFields, function(field) {
            const $field = getField(field);

            const validationData = $field.data('validation') || {};
            const isRequired = isArray ? _.indexOf(requiredFields, field) !== -1 : field === requiredFields;
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

    const resolveFields = function(list) {
        return _.uniq(_.flatten(_.values(list)));
    };

    const validate = function($field, params, silent) {
        const requiredFields = params.deps[$field.val()];
        const allFields = resolveFields(params.deps);

        resetFieldStatus(allFields, requiredFields, silent);
    };

    return [
        'Oro\\Bundle\\ContactUsBundle\\Validator\\ContactRequestCallbackValidator',
        function(value, element, params) {
            const event = 'change.ContactRequestCallbackValidator';
            const $field = getField(params.target);
            $field.off(event).on(event, function() {
                validate($field, params);
            });
            validate($field, params, true);

            return true;
        }
    ];
});
