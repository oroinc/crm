define(function(require) {
    'use strict';

    var OpportunityCardView;
    var CardView = require('orodatagrid/js/app/views/board/card-view');

    OpportunityCardView = CardView.extend({
        className: 'opportunity-card-view card-view',
        template: require('tpl!../../../../templates/board/opportunity-card-view.html')
    });

    return OpportunityCardView;
});
