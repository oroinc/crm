import CardView from 'orodatagrid/js/app/views/board/card-view';
import multiCurrencyFormatter from 'orocurrency/js/formatter/multi-currency';
import template from 'tpl-loader!../../../../templates/board/opportunity-card-view.html';

const OpportunityCardView = CardView.extend({
    className: 'opportunity-card-view card-view',
    template,

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

export default OpportunityCardView;
