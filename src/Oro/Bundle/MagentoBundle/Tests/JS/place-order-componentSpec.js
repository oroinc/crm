/* global ORO_ORDER_EMBED_API */
define(function(require) {
    'use strict';

    const $ = require('jquery');
    const placeOrderComponentModuleInjector = require('inject-loader!oromagento/js/app/components/place-order-component');

    describe('Place Order Component', function() {
        let messenger;
        let PlaceOrderComponent;

        beforeEach(function() {
            window.setFixtures(
                '<div id="container">' +
                    '<iframe data-src="test"></iframe>' +
                '</div>'
            );

            messenger = jasmine.createSpyObj('messenger', ['notificationFlashMessage']);
            PlaceOrderComponent = placeOrderComponentModuleInjector({
                'oroui/js/messenger': messenger
            });
        });

        afterEach(function() {
            ORO_ORDER_EMBED_API = {};
        });

        it('Initialize', function() {
            expect(ORO_ORDER_EMBED_API).toEqual({});

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
            const errorMessage = 'Custom Error message.';

            new PlaceOrderComponent({
                el: '#container',
                errorMessage: errorMessage
            });

            expect(messenger.notificationFlashMessage).toHaveBeenCalledWith('error', errorMessage);
        });
    });
});
