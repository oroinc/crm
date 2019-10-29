define([
    'underscore',
    'orotranslation/js/translator',
    'jquery',
    'oroui/js/mediator',
    'oroui/js/delete-confirmation',
    '../../entity-management/entity-component-view',
    'jquery.select2'
], function(_, __, $, mediator, DeleteConfirmation, EntityComponentView) {
    'use strict';

    /** @type {Object.<Backbone.Collection>} **/
    let entitiesCollection;

    /** @const */
    const UPDATE_MARKER = 'formUpdateMarker';

    /**
     * Initialize "Entities selection" component
     *
     * @param {string} selector
     * @param {Array.<{object}>} metadata
     * @param {Array.} lockedEntities
     */
    function initializeEntityComponent(selector, metadata, lockedEntities) {
        const $storageEl = $(selector);
        const value = $storageEl.val();
        const entities = value ? JSON.parse(value) : [];

        if (entities.length === 0) {
            return;
        }

        const entityComponentView = new EntityComponentView({
            data: entities,
            mode: EntityComponentView.prototype.MODES.EDIT_MODE,
            metadata: metadata,
            lockedEntities: lockedEntities
        });

        entityComponentView.render();
        $storageEl.after(entityComponentView.$el);
        entitiesCollection = entityComponentView.collection;
        entitiesCollection.on('add remove reset', function updateStorage() {
            $storageEl.val(JSON.stringify(entitiesCollection.pluck('name')));
        });
    }

    /**
     * Initialize "Channel type" component, and handle page reload
     *
     * @param {string} selector
     * @param {Object.<string, *>} fields
     */
    function initializeChannelTypeComponent(selector, fields) {
        const $el = $(selector);
        const $form = $el.parents('form');

        /**
         * Get serialized form string with current element value excluded.
         *
         * @returns {String}
         */
        const getFormState = function() {
            $el.attr('disabled', true);
            const result = $form.serialize();
            $el.attr('disabled', false);

            return result;
        };
        const formStartState = getFormState();
        const startChannelType = $el.val();

        const isAllowOpenConfirmDialog = function() {
            return startChannelType !== '' && getFormState() !== formStartState;
        };

        const processChangeType = function() {
            const $form = $el.parents('form');
            const elementNames = _.map(fields, function(elementIdentifier) {
                return $(elementIdentifier).attr('name');
            });

            const data = _.filter($form.serializeArray(), function(field) {
                return _.indexOf(elementNames, field.name) !== -1;
            });
            data.push({name: UPDATE_MARKER, value: 1});

            const event = {formEl: $form, data: data, reloadManually: true};
            mediator.trigger('channelFormReload:before', event);

            if (event.reloadManually) {
                mediator.execute('submitPage', {
                    url: $form.attr('action'),
                    type: $form.attr('method'),
                    data: $.param(data)
                });
            }
        };

        $el.on('change', function changeTypeHandler(e) {
            const prevEl = e.removed;
            const confirm = new DeleteConfirmation({
                title: __('oro.channel.confirmation.title'),
                okText: __('oro.channel.confirmation.agree'),
                content: __('oro.channel.confirmation.text')
            });

            confirm.on('ok', processChangeType);
            confirm.on('cancel', function revertChanges() {
                $el.select2('val', prevEl.id);
            });

            if (isAllowOpenConfirmDialog()) {
                confirm.open();
            } else {
                processChangeType();
            }
        });
    }

    /**
     * Initialize channel form
     *
     * @param {Object} options
     */
    return function(options) {
        let lockedEntities = [];

        if (!_.isArray(options.customerIdentity)) {
            lockedEntities = [options.customerIdentity];
        } else {
            lockedEntities = options.customerIdentity;
        }

        initializeEntityComponent(options.channelEntitiesEl, options.entitiesMetadata, lockedEntities);
        initializeChannelTypeComponent(options.channelTypeEl, options.fields);

        options._sourceElement.remove();
    };
});
