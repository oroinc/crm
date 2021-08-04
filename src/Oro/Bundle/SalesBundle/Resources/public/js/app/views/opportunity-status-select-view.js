define(function(require) {
    'use strict';

    const _ = require('underscore');
    const BaseView = require('oroui/js/app/views/base/view');

    const OpportunityStatusSelectView = BaseView.extend({
        /**
         * @inheritdoc
         */
        constructor: function OpportunityStatusSelectView(options) {
            OpportunityStatusSelectView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            OpportunityStatusSelectView.__super__.initialize.call(this, options);

            this.options = _.defaults(options || {}, this.options);

            this.render();
        },

        render: function() {
            this.initLayout().then(this.afterLayoutInit.bind(this));
        },

        afterLayoutInit: function() {
            const status = this.$('select[data-name="field__status"]');
            const probability = this.$('input[data-name="field__probability"]');
            const defaultProbabilities = status.data('probabilities');
            let shouldChangeProbability = false;

            if (defaultProbabilities.hasOwnProperty(status.val())) {
                if (parseFloat(defaultProbabilities[status.val()]) === parseFloat(probability.val())) {
                    shouldChangeProbability = true;
                }
            }

            probability.on('change', function(e) {
                shouldChangeProbability = false;
            });

            status.on('change', function(e) {
                const val = status.val();

                if (!shouldChangeProbability) {
                    return;
                }

                if (defaultProbabilities.hasOwnProperty(val)) {
                    probability.val(defaultProbabilities[val]);
                }
            });
        }
    });

    return OpportunityStatusSelectView;
});
