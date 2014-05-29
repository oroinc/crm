<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller\Stub\StubCartsIterator;
use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OrderPlaceController extends WebTestCase
{
    /** @var Channel */
    protected static $channel;

    /** @var Cart */
    protected static $cart;

    /** @var Order */
    protected static $order;

    /** @var Customer */
    protected static $customer;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $soapTransport;

    public function setUp()
    {
        $this->initClient(array('debug' => false), $this->generateBasicAuthHeader(), true);
        $this->loadFixtures(
            array(
                'OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel',
            ),
            true
        );

        $this->soapTransport = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport')
            ->setMethods(['init', 'call', 'getCarts', 'getCustomers', 'getOrders'])
            ->disableOriginalConstructor()->getMock();

        $this->getContainer()->set('orocrm_magento.transport.soap_transport', $this->soapTransport);
    }

    public function tearDown()
    {
        self::$channel  = null;
        self::$cart     = null;
        self::$order    = null;
        self::$customer = null;
    }

    protected function postFixtureLoad()
    {
        self::$channel  = $this->getChannel();
        self::$cart     = $this->getCartByChannel(self::$channel);
        self::$order    = $this->getOrderByChannel(self::$channel);
        self::$customer = $this->getCustomerByChannel(self::$channel);
    }

    /**
     * @return Channel|null
     */
    protected function getChannel()
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroIntegrationBundle:Channel')
            ->findOneByName('Demo Web store');
    }

    /**
     * @param Channel $channel
     *
     * @return Cart|null
     */
    protected function getCartByChannel(Channel $channel)
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:Cart')
            ->findOneByChannel($channel);
    }

    /**
     * @param Channel $channel
     *
     * @return mixed
     */
    protected function getOrderByChannel(Channel $channel)
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:Order')
            ->findOneByChannel($channel);
    }

    /**
     * @param Channel $channel
     *
     * @return Customer|null
     */
    protected function getCustomerByChannel(Channel $channel)
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:Customer')
            ->findOneByChannel($channel);
    }


    /*public function testCartAction()
    {
        $widgetId = '2w45254tst4562';
        $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_magento_orderplace_cart',
                [
                    'id' => self::$cart->getId(),
                    '_widgetContainer' => 'block',
                    '_wid' => $widgetId
                ]
            )
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('iframe', $result->getContent());
        $this->assertContains('orderPlaceFrame', $result->getContent());
        $this->assertContains($widgetId, $result->getContent());
    }*/


    public function testSyncAction()
    {
        $newCart = $this->getModifiedCart(self::$cart, self::$customer);

        $cartIterator  = new StubCartsIterator([$newCart]);
        $orderIterator = new \ArrayIterator([]);

        $this->soapTransport->expects($this->any())->method('call');
        $this->soapTransport->expects($this->once())->method('getCarts')->will($this->returnValue($cartIterator));
        $this->soapTransport->expects($this->once())->method('getOrders')->will($this->returnValue($orderIterator));

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_magento_orderplace_sync', ['id' => self::$cart->getId()])
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);
        $arrayJson = json_decode($result->getContent(), 1);
        $this->assertEquals($arrayJson['statusType'], 'success');
        $this->assertEquals($arrayJson['message'], 'Data successfuly synchronized.');
        $this->assertEquals(
            $arrayJson['url'],
            $this->getUrl('orocrm_magento_order_view', ['id' => self::$order->getId()])
        );
    }

    public function gridProvider()
    {
        return [ /*
            'Magento cart grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cart-grid'
                    ],
                    'gridFilters'    => [],
                    'channelName'    => 'Demo Web store',
                    'verifying'      => [
                        'firstName'  => 'John',
                        'lastName'   => 'Doe',
                        'email'      => 'email@email.com',
                        'regionName' => 'Arizona'
                    ],
                    'isResult'       => true
                ],
            ],*/
            'Cart item grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cartitem-grid',
                        'id'       => 'id',
                    ],
                    'gridFilters'    => [],
                    'channelName'    => 'Demo Web store',
                    'verifying'      => [
                        'sku'            => 'sku',
                        'qty'            => 0,
                        'rowTotal'       => '$100.00',
                        'taxAmount'      => '$10.00',
                        'discountAmount' => '$0.00'
                    ],
                    'isResult'       => true
                ],
            ],
        ];
    }

    /**
     * @depends testSyncAction
     */
    public function testSyncedCartView()
    {
        $this->client->request('GET', $this->getUrl('orocrm_magento_cart_view', ['id' => self::$cart->getId()]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('Cart Information', $result->getContent());
        $this->assertContains('email@email.com', $result->getContent());

        $this->assertContains('Customer Information', $result->getContent());
        $this->assertContains('test@example.com', $result->getContent());

    }

    /*
        public function testCustomerSyncAction()
        {
            $this->client->request(
                'GET',
                $this->getUrl('orocrm_magento_orderplace_customer_sync', ['id' => self::$customer->getId()])
            );

            $result = $this->client->getResponse();
            $this->assertJsonResponseStatusCodeEquals($result, 200);
            $arrayJson = json_decode($result->getContent(), 1);
            $this->assertEquals($arrayJson['statusType'], 'success');
            $this->assertEquals($arrayJson['message'], 'Data successfuly synchronized.');
            $this->assertEquals(
                $arrayJson['url'],
                $this->getUrl('orocrm_magento_order_view', ['id' => self::$order->getId()])
            );
        }
    */

    protected function getModifiedCart(Cart $cart, Customer $customer)
    {
        return array(
            'entity_id'                   => $cart->getOriginId(),
            'store_id'                    => $cart->getStore()->getOriginId(),
            'created_at'                  => '2014-04-22 10:41:43',
            'updated_at'                  => '2014-05-29 08:52:33',
            'is_active'                   => true,
            'is_virtual'                  => false,
            'is_multi_shipping'           => false,
            'items_count'                 => '2',
            'items_qty'                   => '6.0000',
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
            'customer_email'              => 'TEST@PRODUCT.com',
            'customer_firstname'          => 'TEST@PRODUCT.com',
            'customer_lastname'           => '12312312',
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
            'payment'                     => [],
            'store_code'                  => $cart->getStore()->getCode(),
            'store_storename'             => $cart->getStore()->getName(),
            'store_website_id'            => $cart->getStore()->getWebsite()->getOriginId(),
            'store_website_code'          => $cart->getStore()->getWebsite()->getCode(),
            'store_website_name'          => $cart->getStore()->getWebsite()->getName(),
            'customer_group_code'         => 'General',
            'customer_group_name'         => 'General',
        );
    }
}
