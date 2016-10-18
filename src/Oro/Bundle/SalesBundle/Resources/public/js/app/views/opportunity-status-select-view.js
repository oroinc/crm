define(function(require) {
    'use strict';

    var OpportunityStatusSelectView;
    var _ = require('underscore');
    var BaseView = require('oroui/js/app/views/base/view');

    OpportunityStatusSelectView = BaseView.extend({

        initialize: function(options) {
            OpportunityStatusSelectView.__super__.initialize.apply(this, arguments);

            this.options = _.defaults(options || {}, this.options);

            this.render();
        },

        render: function() {
            this.initLayout().then(_.bind(this.afterLayoutInit, this));
        },

        afterLayoutInit: function() {
            var status = this.$('select[data-name="field__status"]');
            var probability = this.$('input[data-name="field__probability"]');
            var defaultProbabilities = status.data('probabilities');
            var shouldChangeProbability = false;

            if (defaultProbabilities.hasOwnProperty(status.val())) {
                if (parseFloat(defaultProbabilities[status.val()]) === parseFloat(probability.val())) {
                    shouldChangeProbability = true;
                }
            }

            probability.on('change', function(e) {
                shouldChangeProbability = false;
            });

            status.on('change', function(e) {
                var val = status.val();

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
