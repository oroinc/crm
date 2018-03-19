define(function(require) {
    'use strict';

    var AccountContactComponent;
    var MultipleEntityComponent = require('oroform/js/multiple-entity/component');
    var nameFormatter = require('orolocale/js/formatter/name');

    AccountContactComponent = MultipleEntityComponent.extend({
        /**
         * @inheritDoc
         */
        constructor: function AccountContactComponent() {
            AccountContactComponent.__super__.constructor.apply(this, arguments);
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
