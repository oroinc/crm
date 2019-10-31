define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');

    const PlaceOrderView = BaseView.extend({
        events: {
            load: 'onFrameLoad'
        },

        /**
         * @inheritDoc
         */
        constructor: function PlaceOrderView(options) {
            PlaceOrderView.__super__.constructor.call(this, options);
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            BaseView.__super__.initialize.call(this, options);

            const $frame = this.$el;

            $frame.attr('src', $frame.data('src').replace(/^https?:/gi, ''));
        },

        onFrameLoad: function(e) {
            const $frame = this.$el;
            const offset = $frame.closest('.ui-dialog').find('.ui-dialog-titlebar').outerHeight() || 0;

            $frame.addClass('loaded').parent().css({top: offset});

            this.trigger('frameLoaded');
        }
    });

    return PlaceOrderView;
});
