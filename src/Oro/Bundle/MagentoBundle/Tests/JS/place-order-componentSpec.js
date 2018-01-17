var ORO_ORDER_EMBED_API = {};

define(function(require) {
    'use strict';

    var PlaceOrderComponent = require('oromagento/js/app/components/place-order-component');
    var $ = require('jquery');
    var requireJsExposure = require('requirejs-exposure');
    var exposure = requireJsExposure.disclose('oromagento/js/app/components/place-order-component');

    describe('Place Order Component', function() {
        var messenger;

        beforeEach(function() {
            window.setFixtures([
                '<div id="container">',
                    '<iframe data-src="test"></iframe>',
                '</div>'
            ].join(''));

            messenger = jasmine.createSpyObj('messenger', ['notificationFlashMessage']);
            exposure.substitute('messenger').by(messenger);
        });

        afterEach(function() {
            exposure.recover('messenger');
        });

        it('Initialize', function() {
            expect(ORO_ORDER_EMBED_API).toEqual({});

            /*jshint nonew: false */
            new PlaceOrderComponent({
                _sourceElement: $('#container'),
                wid: 'wid-123',
                cartSyncURL: 'http://cartSyncURL',
                customerSyncURL: 'http://customerSyncURL'
            });

            expect(ORO_ORDER_EMBED_API.success).toEqual(jasmine.any(Function));
            expect(ORO_ORDER_EMBED_API.error).toEqual(jasmine.any(Function));
        });

        it('Handle Error', function() {
            var errorMessage = 'Custom Error message.';

            /*jshint nonew: false */
            new PlaceOrderComponent({
                el: '#container',
                errorMessage: errorMessage
            });

            expect(messenger.notificationFlashMessage).toHaveBeenCalledWith('error', errorMessage);
        });
    });
});
