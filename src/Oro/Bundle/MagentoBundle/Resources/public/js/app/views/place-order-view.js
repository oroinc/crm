define(function(require) {
    'use strict';

    var PlaceOrderView;
    var BaseView = require('oroui/js/app/views/base/view');

    PlaceOrderView = BaseView.extend({
        events: {
            'load': 'onFrameLoad'
        },

        initialize: function() {
            BaseView.__super__.initialize.apply(this, arguments);

            var $frame = this.$el;

            $frame.attr('src', $frame.data('src').replace(/^https?:/gi, ''));
        },

        onFrameLoad: function(e) {
            var $frame = this.$el;
            var offset = $frame.closest('.ui-dialog').find('.ui-dialog-titlebar').outerHeight() || 0;

            $frame.addClass('loaded').parent().css({'top': offset});

            this.trigger('frameLoaded');
        }
    });

    return PlaceOrderView;
});
