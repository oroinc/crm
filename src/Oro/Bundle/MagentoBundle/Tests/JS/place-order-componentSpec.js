const ORO_ORDER_EMBED_API = {};

define(function(require) {
    'use strict';

    const PlaceOrderComponent = require('oromagento/js/app/components/place-order-component');
    const $ = require('jquery');
    const jsmoduleExposure = require('jsmodule-exposure');
    const exposure = jsmoduleExposure.disclose('oromagento/js/app/components/place-order-component');

    xdescribe('Place Order Component', function() {
        let messenger;

        beforeEach(function() {
            window.setFixtures(
                '<div id="container">' +
                    '<iframe data-src="test"></iframe>' +
                '</div>'
            );

            messenger = jasmine.createSpyObj('messenger', ['notificationFlashMessage']);
            exposure.substitute('messenger').by(messenger);
        });

        afterEach(function() {
            exposure.recover('messenger');
        });

        it('Initialize', function() {
            expect(ORO_ORDER_EMBED_API).toEqual({});

            /* jshint nonew: false */
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

            /* jshint nonew: false */
            new PlaceOrderComponent({
                el: '#container',
                errorMessage: errorMessage
            });

            expect(messenger.notificationFlashMessage).toHaveBeenCalledWith('error', errorMessage);
        });
    });
});
