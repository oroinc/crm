define(function(require) {
    'use strict';

    var UpdatePageView;
    var _ = require('underscore');
    var BaseView = require('oroui/js/app/views/base/view');

    UpdatePageView = BaseView.extend({

        initialize: function(options) {
            UpdatePageView.__super__.initialize.apply(this, arguments);

            this.options = _.defaults(options || {}, this.options);

            this.render();
        },

        render: function() {
            this.initLayout().then(_.bind(this.afterLayoutInit, this));
        },

        afterLayoutInit: function() {
            var status = this.pageComponent('oro_sales_opportunity_form_status').view.$el;
            var probability = this.$('input[data-name="field__probability"]:enabled');
            var probabilities = status.data('probabilities');
            var shouldChangeProbability = false;

            // probability field might be missing or disabled
            if (0 === probability.length) {
                return;
            }

            if (probabilities.hasOwnProperty(status.val())) {
                if (parseFloat(probabilities[status.val()]) === parseFloat(probability.val())) {
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

                if (probabilities.hasOwnProperty(val)) {
                    probability.val(probabilities[val]);
                }
            });
        }
    });

    return UpdatePageView;
});
