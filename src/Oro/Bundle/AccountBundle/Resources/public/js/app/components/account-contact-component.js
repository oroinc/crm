define(function(require) {
    'use strict';

    const MultipleEntityComponent = require('oroform/js/multiple-entity/component');
    const nameFormatter = require('orolocale/js/formatter/name');

    const AccountContactComponent = MultipleEntityComponent.extend({
        /**
         * @inheritdoc
         */
        constructor: function AccountContactComponent(options) {
            AccountContactComponent.__super__.constructor.call(this, options);
        },

        _getLabel: function(model) {
            return nameFormatter.format(model.toJSON());
        },

        _getExtraData: function(model) {
            return [
                {
                    label: 'Phone',
                    value: model.get('primaryPhone')
                },
                {
                    label: 'Email',
                    value: model.get('primaryEmail')
                }
            ];
        }
    });

    return AccountContactComponent;
});
