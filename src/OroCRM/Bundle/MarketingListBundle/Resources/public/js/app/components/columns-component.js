define(function(require) {
    'use strict';

    var $ = require('jquery');
    var _ = require('underscore');
    var mediator = require('oroui/js/mediator');
    var routing = require('routing');
    var contactInformationFieldsCache = {};
    var $entityEl;
    var $fieldsListEl;

    var fillContactInformationFieldsCache = function(contactInformationFields) {
        _.each(contactInformationFields, function(field) {
            contactInformationFieldsCache[field.name] = field.contact_information_type;
        });
    };

    var updateContactInformationFieldsInfo = function(contactInformationFields) {
        var list = $('<ul/>');
        _.each(contactInformationFields, function(field) {
            list.append($('<li/>').html(field.label));
        });
        $fieldsListEl.html(list)
            .closest('.alert').toggleClass('has-fields', contactInformationFields.length > 0);
    };

    var updateContactInformationFields = function(contactInformationFields) {
        updateContactInformationFieldsInfo(contactInformationFields);
        fillContactInformationFieldsCache(contactInformationFields);
    };

    var loadEntityContactInformationFields = function(entity) {
        if (entity) {
            $.ajax({
                url: routing.generate('orocrm_api_entity_marketinglist_contact_information_fields'),
                data: {'entity': entity},
                success: updateContactInformationFields
            });
        }
    };

    var contactInformationRender = function(model, element, type) {
        var icon;
        if (type) {
            if (type === 'phone') {
                icon = 'icon-phone';
            } else if (type === 'email') {
                icon = 'icon-envelope';
            }

            var item = element.find('[data-cid="' + model.cid + '"] .name-cell');

            if (!item.hasClass('has-icon')) {
                item
                    .addClass('has-icon')
                    .prepend($('<i/>').addClass(icon));
            }
        }
    };

    var getFieldContactInformationType = function(model, element) {
        var fieldName = model.get('name');
        if (contactInformationFieldsCache.hasOwnProperty(fieldName)) {
            contactInformationRender(model, element, contactInformationFieldsCache[fieldName]);
        } else if (fieldName.indexOf(':') > -1) {
            $.ajax({
                url: routing.generate('orocrm_api_contact_marketinglist_information_field_type'),
                data: {
                    'entity': $entityEl.select2('val'),
                    'field': fieldName
                },
                success: function(type) {
                    contactInformationFieldsCache[fieldName] = type;
                    contactInformationRender(model, element, contactInformationFieldsCache[fieldName]);
                }
            });
        }
    };

    return function(options) {
        var $form = $(options.formSelector);
        $entityEl = $form.find(options.entityChoiceSelector);
        $fieldsListEl = $form.find(options.fieldsChoiceSelector);

        if (!_.isEmpty(options.contactInformationFields)) {
            updateContactInformationFields(options.contactInformationFields);
        }

        $entityEl.on('change', function(e) {
            loadEntityContactInformationFields(e.val);
        });

        mediator.on('items-manager:table:add:item-container', function(collection, model, element) {
            getFieldContactInformationType(model, element);
        });
    };
});
