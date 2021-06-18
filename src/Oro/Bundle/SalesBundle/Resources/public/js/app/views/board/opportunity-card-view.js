define(function(require) {
    'use strict';

    const CardView = require('orodatagrid/js/app/views/board/card-view');
    const multiCurrencyFormatter = require('orocurrency/js/formatter/multi-currency');

    const OpportunityCardView = CardView.extend({
        className: 'opportunity-card-view card-view',
        template: require('tpl-loader!../../../../templates/board/opportunity-card-view.html'),

        /**
         * @inheritdoc
         */
        constructor: function OpportunityCardView(options) {
            OpportunityCardView.__super__.constructor.call(this, options);
        },

        getTemplateData: function() {
            const data = OpportunityCardView.__super__.getTemplateData.call(this);
            const budgetAmount = multiCurrencyFormatter.unformatMultiCurrency(data.budgetAmount);
            data.budgetAmount = budgetAmount.amount;
            data.budgetCurrency = budgetAmount.currency;
            return data;
        }
    });

    return OpportunityCardView;
});
