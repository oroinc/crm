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
    var entitiesCollection;

    /** @const */
    var UPDATE_MARKER = 'formUpdateMarker';

    /**
     * Initialize "Entities selection" component
     *
     * @param {string} selector
     * @param {Array.<{object}>} metadata
     * @param {Array.} lockedEntities
     */
    function initializeEntityComponent(selector, metadata, lockedEntities) {
        var $storageEl = $(selector);
        var value = $storageEl.val();
        var entities = value ? JSON.parse(value) : [];

        if (entities.length === 0) {
            return;
        }

        var entityComponentView = new EntityComponentView({
            data: entities,
            mode: EntityComponentView.prototype.MODES.EDIT_MODE,
            metadata: metadata,
            lockedEntities: lockedEntities
        });

        entityComponentView.render();
        $storageEl.after(entityComponentView.$el);
        entitiesCollection = entityComponentView.collection;
        entitiesCollection.on('add remove reset', function updateStorage () {
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
        var $el = $(selector);
        var $form = $el.parents('form');

        /**
         * Get serialized form string with current element value excluded.
         *
         * @returns {String}
         */
        var getFormState = function() {
            $el.attr('disabled', true);
            var result = $form.serialize();
            $el.attr('disabled', false);

            return result;
        };
        var formStartState = getFormState();
        var startChannelType = $el.val();

        var isAllowOpenConfirmDialog = function() {
            return startChannelType !== '' && getFormState() !== formStartState;
        };

        var processChangeType = function() {
            var data;
            var event;
            var $form = $el.parents('form');
            var elementNames = _.map(fields, function(elementIdentifier) {
                return $(elementIdentifier).attr('name');
            });

            data = _.filter($form.serializeArray(), function(field) {
                return _.indexOf(elementNames, field.name) !== -1;
            });
            data.push({name: UPDATE_MARKER, value: 1});

            event = {formEl: $form, data: data, reloadManually: true};
            mediator.trigger('channelFormReload:before', event);

            if (event.reloadManually) {
                mediator.execute('submitPage', {
                    url:  $form.attr('action'),
                    type: $form.attr('method'),
                    data: $.param(data)
                });
            }
        };

        $el.on('change', function changeTypeHandler(e) {
            var prevEl = e.removed;
            var confirm = new DeleteConfirmation({
                title:   __('oro.channel.confirmation.title'),
                okText:  __('oro.channel.confirmation.agree'),
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

        var lockedEntities = [];

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
