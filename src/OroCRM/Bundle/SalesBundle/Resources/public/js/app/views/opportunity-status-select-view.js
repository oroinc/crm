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

            status.on('change', function(e) {
                var val = status.val();
                var defaultProbability;

                if (defaultProbabilities.hasOwnProperty(val)) {
                    defaultProbability = defaultProbabilities[val] * 100;
                    probability.val(defaultProbability)
                }
            });
        }
    });

    return OpportunityStatusSelectView;
});
