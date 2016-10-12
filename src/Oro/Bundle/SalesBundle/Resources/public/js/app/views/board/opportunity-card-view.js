define(function(require) {
    'use strict';

    var OpportunityCardView;
    var CardView = require('orodatagrid/js/app/views/board/card-view');
    var multiCurrencyFormatter = require('orocurrency/js/formatter/multi-currency');

    OpportunityCardView = CardView.extend({
        className: 'opportunity-card-view card-view',
        template: require('tpl!../../../../templates/board/opportunity-card-view.html'),
        getTemplateData: function() {
            var data = OpportunityCardView.__super__.getTemplateData.call(this, arguments);
            var budgetAmount = multiCurrencyFormatter.unformatMultiCurrency(data.budgetAmount);
            data.budgetAmount = budgetAmount.amount;
            data.budgetCurrency = budgetAmount.currency;
            return data;
        }
    });

    return OpportunityCardView;
});
