<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Stub\StubIterator;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class OrderPlaceControllerTest extends WebTestCase
{
    const TEST_NEW_EMAIL       = 'new@email.com';
    const TEST_NEW_GUEST_EMAIL = 'guest@email.com';
    const TEST_NEW_ITEMS_QTY   = 444;

    /** @var Channel */
    protected $channel;

    /** @var Cart */
    protected $cart;

    /** @var Order */
    protected $order;

    /** @var Cart */
    protected $guestCart;

    /** @var Order */
    protected $guestOrder;

    /** @var Customer */
    protected $customer;

    /** @var SoapTransport|\PHPUnit\Framework\MockObject\MockObject */
    protected $soapTransport;

    protected function setUp(): void
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel']);

        $this->soapTransport = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport')
            ->setMethods(['init', 'call', 'getCarts', 'getCustomers', 'getOrders'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->getContainer()->set('oro_magento.transport.soap_transport.test', $this->soapTransport);
    }

    protected function postFixtureLoad()
    {
        $this->channel  = $this->getReference('integration');
        $this->cart     = $this->getReference('cart');
        $this->order    = $this->getReference('order');
        $this->customer = $this->getReference('customer');
        $this->guestCart = $this->getReference('guestCart');
        $this->guestOrder = $this->getReference('guestOrder');
    }

    public function testCartAction()
    {
        $widgetId = '2w45254tst4562';
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_magento_orderplace_cart',
                [
                    'id'               => $this->cart->getId(),
                    '_widgetContainer' => 'block',
                    '_wid'             => $widgetId
                ]
            )
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('iframe', $result->getContent());
        static::assertStringContainsString('orderPlaceFrame', $result->getContent());
        static::assertStringContainsString($widgetId, $result->getContent());
    }

    public function testSyncAction()
    {
        $newCart = $this->getModifiedCartData($this->cart, $this->customer);

        $cartIterator = new StubIterator([$newCart]);
        $orderIterator = new StubIterator(
            [
                [
                    'increment_id' => $this->order->getIncrementId(),
                    'quote_id' => $this->cart->getOriginId(),
                    'customer_id' => $this->customer->getOriginId(),
                ],
            ]
        );
        $customerIterator = new StubIterator([]);

        $this->soapTransport->expects($this->any())->method('call');
        $this->soapTransport->expects($this->once())->method('getCarts')->will($this->returnValue($cartIterator));
        $this->soapTransport->expects($this->once())->method('getOrders')->will($this->returnValue($orderIterator));
        $this->soapTransport->expects($this->any())->method('getCustomers')
            ->will($this->returnValue($customerIterator));

        $this->ajaxRequest(
            'POST',
            $this->getUrl('oro_magento_orderplace_new_cart_order_sync', ['id' => $this->cart->getId()]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);
        $arrayJson = json_decode($result->getContent(), 1);
        $this->assertEquals('success', $arrayJson['statusType']);
        $this->assertEquals('Data successfully synchronized.', $arrayJson['message']);
        $this->assertEquals(
            $arrayJson['url'],
            $this->getUrl('oro_magento_order_view', ['id' => $this->order->getId()])
        );

        $this->client->request('GET', $this->getUrl('oro_magento_cart_view', ['id' => $this->cart->getId()]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $resultContent = $result->getContent();
        static::assertStringContainsString('Cart Information', $resultContent);
        static::assertStringContainsString(self::TEST_NEW_EMAIL, $resultContent);
        static::assertStringContainsString((string)self::TEST_NEW_ITEMS_QTY, $resultContent);
        static::assertStringContainsString('Expired', $resultContent);

        static::assertStringContainsString('Customer Information', $resultContent);
        static::assertStringContainsString('test@example.com', $resultContent);
    }

    /**
     * @param Cart     $cart
     * @param Customer $customer
     *
     * @return array
     */
    protected function getModifiedCartData(Cart $cart, Customer $customer)
    {
        return [
            'entity_id'                   => $cart->getOriginId(),
            'store_id'                    => $cart->getStore()->getOriginId(),
            'created_at'                  => '2014-04-22 10:41:43',
            'updated_at'                  => '2014-05-29 08:52:33',
            'is_active'                   => false,
            'is_virtual'                  => false,
            'is_multi_shipping'           => false,
            'items_count'                 => '2',
            'items_qty'                   => self::TEST_NEW_ITEMS_QTY,
            'orig_order_id'               => '0',
            'store_to_base_rate'          => '1.0000',
            'store_to_quote_rate'         => '1.0000',
            'base_currency_code'          => 'USD',
            'store_currency_code'         => 'USD',
            'quote_currency_code'         => 'USD',
            'grand_total'                 => '855.0000',
            'base_grand_total'            => '855.0000',
            'customer_id'                 => $customer->getOriginId(),
            'customer_tax_class_id'       => '3',
            'customer_group_id'           => '1',
            'customer_email'              => self::TEST_NEW_EMAIL,
            'customer_firstname'          => 'firstname',
            'customer_lastname'           => 'lastname',
            'customer_note_notify'        => '1',
            'customer_is_guest'           => '0',
            'remote_ip'                   => '82.117.235.210',
            'global_currency_code'        => 'USD',
            'base_to_global_rate'         => '1.0000',
            'base_to_quote_rate'          => '1.0000',
            'subtotal'                    => '855.0000',
            'base_subtotal'               => '855.0000',
            'subtotal_with_discount'      => '855.0000',
            'base_subtotal_with_discount' => '855.0000',
            'is_changed'                  => '1',
            'trigger_recollect'           => '0',
            'is_persistent'               => '0',
            'shipping_address'            => [],
            'billing_address'             => [],
            'items'                       => [],
            'payment'                     => '',
            'store_code'                  => $cart->getStore()->getCode(),
            'store_storename'             => $cart->getStore()->getName(),
            'store_website_id'            => $cart->getStore()->getWebsite()->getOriginId(),
            'store_website_code'          => $cart->getStore()->getWebsite()->getCode(),
            'store_website_name'          => $cart->getStore()->getWebsite()->getName(),
            'customer_group_code'         => 'General',
            'customer_group_name'         => 'General',
        ];
    }

    public function testCustomerSyncAction()
    {
        $newCustomerOrder = $this->getModifiedCustomerOrder($this->customer);

        $orderIterator = new StubIterator([$newCustomerOrder]);
        $customerIterator = new StubIterator([]);

        $this->soapTransport->expects($this->any())->method('call');
        $this->soapTransport->expects($this->once())->method('getOrders')->will($this->returnValue($orderIterator));
        $this->soapTransport->expects($this->any())->method('getCustomers')
            ->will($this->returnValue($customerIterator));

        $this->ajaxRequest(
            'POST',
            $this->getUrl('oro_magento_orderplace_new_customer_order_sync', ['id' => $this->customer->getId()]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);
        $arrayJson = json_decode($result->getContent(), 1);
        $this->assertEquals($arrayJson['statusType'], 'success');
        $this->assertEquals($arrayJson['message'], 'Data successfully synchronized.');

        $this->assertEquals(
            $arrayJson['url'],
            $this->getUrl('oro_magento_order_view', ['id' => $this->order->getId()])
        );

        $this->client->request(
            'GET',
            $this->getUrl('oro_magento_customer_view', ['id' => $this->customer->getId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        static::assertStringContainsString('General Information', $result->getContent());
        static::assertStringContainsString('100000307', $result->getContent());
        static::assertStringContainsString('$750', $result->getContent());
        static::assertStringContainsString('pending', $result->getContent());
        static::assertStringContainsString('$755', $result->getContent());
        static::assertStringContainsString('$755', $result->getContent());
    }

    /**
     * @param Customer $customer
     *
     * @return array
     */
    protected function getModifiedCustomerOrder(Customer $customer)
    {
        return [
            'increment_id'         => '100000307',
            'store_id'             => $customer->getStore()->getOriginId(),
            'created_at'           => '2014-05-29 16:41:43',
            'updated_at'           => '2014-05-29 16:41:43',
            'customer_id'          => $customer->getOriginId(),
            'tax_amount'           => '0.0000',
            'shipping_amount'      => '5.0000',
            'discount_amount'      => '0.0000',
            'subtotal'             => '750.0000',
            'grand_total'          => '755.0000',
            'total_qty_ordered'    => '1.0000',
            'base_tax_amount'      => '0.0000',
            'base_shipping_amount' => '5.0000',
            'base_discount_amount' => '0.0000',
            'base_subtotal'        => '750.0000',
            'base_grand_total'     => '755.0000',
            'billing_address_id'   => '603',
            'billing_firstname'    => 'asdf',
            'billing_lastname'     => 'asdf',
            'shipping_address_id'  => '604',
            'shipping_firstname'   => 'asdf',
            'shipping_lastname'    => 'asdf',
            'billing_name'         => 'asdf asdf',
            'shipping_name'        => 'asdf asdf',
            'store_to_base_rate'   => '1.0000',
            'store_to_order_rate'  => '1.0000',
            'base_to_global_rate'  => '1.0000',
            'base_to_order_rate'   => '1.0000',
            'weight'               => '0.3000',
            'store_name'           => $customer->getStore()->getName(),
            'status'               => 'pending',
            'state'                => 'new',
            'global_currency_code' => 'USD',
            'base_currency_code'   => 'USD',
            'store_currency_code'  => 'USD',
            'order_currency_code'  => 'USD',
            'shipping_method'      => 'flatrate_flatrate',
            'shipping_description' => 'Flat Rate - Fixed',
            'customer_email'       => 'valibaba@pochta.com',
            'customer_firstname'   => 'asdf',
            'customer_lastname'    => 'asdf',
            'quote_id'             => '100',
            'is_virtual'           => '0',
            'customer_group_id'    => '1',
            'customer_note_notify' => '0',
            'customer_is_guest'    => '0',
            'email_sent'           => '1',
            'order_id'             => '302',
            'shipping_address'     => [],
            'billing_address'      => [],
            'items'                => [],
            'payment'              => '',
            'status_history'       => [],
            'store_code'           => $customer->getStore()->getCode(),
            'store_storename'      => $customer->getStore()->getName(),
            'store_website_id'     => $customer->getStore()->getWebsite()->getOriginId(),
            'store_website_code'   => $customer->getStore()->getWebsite()->getCode(),
            'store_website_name'   => $customer->getStore()->getWebsite()->getName(),
        ];
    }

    public function testSyncGuestOrderAction()
    {
        $newCart = $this->getModifiedGuestCartData($this->guestCart);
        $cartIterator = new StubIterator([$newCart]);

        $newCustomerOrder = $this->getModifiedGuestCustomerOrder($this->guestCart);
        $orderIterator = new StubIterator([$newCustomerOrder]);
        $customerIterator = new StubIterator([]);

        $this->soapTransport->expects($this->any())->method('call');
        $this->soapTransport->expects($this->once())->method('getCarts')->will($this->returnValue($cartIterator));
        $this->soapTransport->expects($this->once())->method('getOrders')->will($this->returnValue($orderIterator));
        $this->soapTransport->expects($this->any())->method('getCustomers')
            ->will($this->returnValue($customerIterator));

        $this->ajaxRequest(
            'POST',
            $this->getUrl('oro_magento_orderplace_new_cart_order_sync', ['id' => $this->guestCart->getId()]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);
        $arrayJson = json_decode($result->getContent(), 1);
        $this->assertEquals('success', $arrayJson['statusType']);
        $this->assertEquals('Data successfully synchronized.', $arrayJson['message']);
        $this->assertEquals(
            $arrayJson['url'],
            $this->getUrl('oro_magento_order_view', ['id' => $this->guestOrder->getId()])
        );

        $this->client->request('GET', $this->getUrl('oro_magento_cart_view', ['id' => $this->guestCart->getId()]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $resultContent = $result->getContent();
        static::assertStringContainsString('Cart Information', $resultContent);
        static::assertStringContainsString(self::TEST_NEW_GUEST_EMAIL, $resultContent);

        static::assertStringContainsString('Customer Information', $resultContent);
        static::assertStringContainsString(self::TEST_NEW_GUEST_EMAIL, $resultContent);

        $this->assertEquals(
            $arrayJson['url'],
            $this->getUrl('oro_magento_order_view', ['id' => $this->guestOrder->getId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $resultContent = $result->getContent();
        static::assertStringContainsString('Customer Information', $resultContent);
        static::assertStringContainsString(self::TEST_NEW_GUEST_EMAIL, $resultContent);
    }

    /**
     * @param Cart $cart
     *
     * @return array
     */
    protected function getModifiedGuestCartData(Cart $cart)
    {
        return [
            'entity_id'                   => $cart->getId(),
            'store_id'                    => $cart->getStore()->getOriginId(),
            'created_at'                  => '2014-04-22 10:41:43',
            'updated_at'                  => '2014-05-29 08:52:33',
            'is_active'                   => false,
            'is_virtual'                  => false,
            'is_multi_shipping'           => false,
            'items_count'                 => '2',
            'items_qty'                   => self::TEST_NEW_ITEMS_QTY,
            'orig_order_id'               => '0',
            'store_to_base_rate'          => '1.0000',
            'store_to_quote_rate'         => '1.0000',
            'base_currency_code'          => 'USD',
            'store_currency_code'         => 'USD',
            'quote_currency_code'         => 'USD',
            'grand_total'                 => '855.0000',
            'base_grand_total'            => '855.0000',
            'customer_tax_class_id'       => '3',
            'customer_group_id'           => '0',
            'customer_email'              => self::TEST_NEW_GUEST_EMAIL,
            'customer_firstname'          => 'Guest firstname',
            'customer_lastname'           => 'Guest lastname',
            'customer_note_notify'        => '1',
            'customer_is_guest'           => '1',
            'remote_ip'                   => '82.117.235.210',
            'global_currency_code'        => 'USD',
            'base_to_global_rate'         => '1.0000',
            'base_to_quote_rate'          => '1.0000',
            'subtotal'                    => '855.0000',
            'base_subtotal'               => '855.0000',
            'subtotal_with_discount'      => '855.0000',
            'base_subtotal_with_discount' => '855.0000',
            'is_changed'                  => '1',
            'trigger_recollect'           => '0',
            'is_persistent'               => '0',
            'shipping_address'            => [],
            'billing_address'             => [],
            'items'                       => [],
            'payment'                     => '',
            'store_code'                  => $cart->getStore()->getCode(),
            'store_storename'             => $cart->getStore()->getName(),
            'store_website_id'            => $cart->getStore()->getWebsite()->getOriginId(),
            'store_website_code'          => $cart->getStore()->getWebsite()->getCode(),
            'store_website_name'          => $cart->getStore()->getWebsite()->getName(),
            'customer_group_code'         => 'NOT LOGGED IN',
            'customer_group_name'         => 'NOT LOGGED IN',
        ];
    }

    /**
     * @param Cart $cart
     *
     * @return array
     */
    protected function getModifiedGuestCustomerOrder(Cart $cart)
    {
        return [
            'increment_id'         => '100000308',
            'store_id'             => $cart->getStore()->getOriginId(),
            'created_at'           => '2014-05-29 16:41:43',
            'updated_at'           => '2014-05-29 16:41:43',
            'tax_amount'           => '0.0000',
            'shipping_amount'      => '5.0000',
            'discount_amount'      => '0.0000',
            'subtotal'             => '750.0000',
            'grand_total'          => '755.0000',
            'total_qty_ordered'    => '1.0000',
            'base_tax_amount'      => '0.0000',
            'base_shipping_amount' => '5.0000',
            'base_discount_amount' => '0.0000',
            'base_subtotal'        => '750.0000',
            'base_grand_total'     => '755.0000',
            'billing_address_id'   => '603',
            'billing_firstname'    => 'Guest asdf',
            'billing_lastname'     => 'Guest asdf',
            'shipping_address_id'  => '604',
            'shipping_firstname'   => 'Guest asdf',
            'shipping_lastname'    => 'Guest asdf',
            'billing_name'         => 'Guest asdf asdf',
            'shipping_name'        => 'Guest asdf asdf',
            'store_to_base_rate'   => '1.0000',
            'store_to_order_rate'  => '1.0000',
            'base_to_global_rate'  => '1.0000',
            'base_to_order_rate'   => '1.0000',
            'weight'               => '0.3000',
            'store_name'           => $cart->getStore()->getName(),
            'status'               => 'pending',
            'state'                => 'new',
            'global_currency_code' => 'USD',
            'base_currency_code'   => 'USD',
            'store_currency_code'  => 'USD',
            'order_currency_code'  => 'USD',
            'shipping_method'      => 'flatrate_flatrate',
            'shipping_description' => 'Flat Rate - Fixed',
            'customer_email'       => self::TEST_NEW_GUEST_EMAIL,
            'customer_firstname'   => 'Guest asdf',
            'customer_lastname'    => 'Guest asdf',
            'quote_id'             => $cart->getOriginId(),
            'is_virtual'           => '0',
            'customer_group_id'    => '0',
            'customer_note_notify' => '0',
            'customer_is_guest'    => '1',
            'email_sent'           => '1',
            'order_id'             => '303',
            'shipping_address'     => [],
            'billing_address'      => [],
            'items'                => [],
            'payment'              => '',
            'status_history'       => [],
            'store_code'           => $cart->getStore()->getCode(),
            'store_storename'      => $cart->getStore()->getName(),
            'store_website_id'     => $cart->getStore()->getWebsite()->getOriginId(),
            'store_website_code'   => $cart->getStore()->getWebsite()->getCode(),
            'store_website_name'   => $cart->getStore()->getWebsite()->getName(),
        ];
    }
}
