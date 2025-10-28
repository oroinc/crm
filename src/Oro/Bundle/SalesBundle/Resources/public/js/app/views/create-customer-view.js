import _ from 'underscore';
import __ from 'orotranslation/js/translator';
import mediator from 'oroui/js/mediator';
import routing from 'routing';
import DialogWidget from 'oro/dialog-widget';
import BaseView from 'oroui/js/app/views/base/view';

const CustomerView = BaseView.extend({
    events: {
        'click button': 'onCreate'
    },

    dialogWidget: null,

    /**
     * @inheritdoc
     */
    constructor: function CustomerView(options) {
        CustomerView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        CustomerView.__super__.initialize.call(this, options);

        this.options = _.defaults(options || {}, this.options);
    },

    onCreate: function() {
        const customer = this.$('[data-customer]').data('customer');
        const routeParams = this.$el.parents()
            .find(this.options.inputSelector)
            .data('select2_query_additional_params') || {};

        this.dialogWidget = new DialogWidget({
            title: __('Create {{ entity }}', {entity: this.$el.text()}),
            url: routing.generate(customer.routeCreate, routeParams),
            stateEnabled: false,
            incrementalPosition: true,
            dialogOptions: {
                modal: true,
                allowMaximize: true,
                width: 1280,
                height: 650
            }
        });

        this.dialogWidget.once('formSave', id => {
            this.dialogWidget.remove();
            this.dialogWidget = null;

            mediator.trigger(
                'customer-dialog:select',
                JSON.stringify({entityClass: customer.className, entityId: id})
            );
        });

        this.dialogWidget.render();
    }
});

export default CustomerView;
