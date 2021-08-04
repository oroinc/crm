define(function(require) {
    'use strict';

    const _ = require('underscore');
    const BaseView = require('oroui/js/app/views/base/view');

    const UpdatePageView = BaseView.extend({
        /**
         * @inheritdoc
         */
        constructor: function UpdatePageView(options) {
            UpdatePageView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritdoc
         */
        initialize: function(options) {
            UpdatePageView.__super__.initialize.call(this, options);

            this.options = _.defaults(options || {}, this.options);

            this.render();
        },

        render: function() {
            this.initLayout().then(this.afterLayoutInit.bind(this));
        },

        afterLayoutInit: function() {
            const status = this.pageComponent('oro_sales_opportunity_form_status').view.$el;
            const probability = this.$('input[data-name="field__probability"]:enabled');
            const probabilities = status.data('probabilities');
            let shouldChangeProbability = false;

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
                const val = status.val();

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
