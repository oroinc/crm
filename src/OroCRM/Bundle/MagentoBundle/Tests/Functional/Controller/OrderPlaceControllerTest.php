<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

use OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller\Stub\StubCartsIterator;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OrderPlaceController extends WebTestCase
{
    const TEST_NEW_EMAIL     = 'new@email.com';
    const TEST_NEW_ITEMS_QTY = 444;
    const TEST_NEW_SUBTOTAL  = '133.33';

    /** @var Channel */
    protected $channel;

    /** @var Cart */
    protected $cart;

    /** @var Order */
    protected $order;

    /** @var Customer */
    protected $customer;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $soapTransport;

    public function setUp()
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader(), true);
        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel'], true);

        $this->soapTransport = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport')
            ->setMethods(['init', 'call', 'getCarts', 'getCustomers', 'getOrders'])
            ->disableOriginalConstructor()->getMock();

        $this->getContainer()->set('orocrm_magento.transport.soap_transport', $this->soapTransport);
    }

    protected function postFixtureLoad()
    {
        $this->channel  = $this->getChannel();
        $this->cart     = $this->getCartByChannel($this->channel);
        $this->order    = $this->getOrderByChannel($this->channel);
        $this->customer = $this->getCustomerByChannel($this->channel);
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

    public function testCartAction()
    {
        $widgetId = '2w45254tst4562';
        $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_magento_orderplace_cart',
                [
                    'id' => $this->cart->getId(),
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
    }

    public function testSyncAction()
    {
        $newCart = $this->getModifiedCartData($this->cart, $this->customer);

        $cartIterator  = new StubCartsIterator([$newCart]);
        $orderIterator = new \ArrayIterator([]);

        $this->soapTransport->expects($this->any())->method('call');
        $this->soapTransport->expects($this->once())->method('getCarts')->will($this->returnValue($cartIterator));
        $this->soapTransport->expects($this->once())->method('getOrders')->will($this->returnValue($orderIterator));

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_magento_orderplace_sync', ['id' => $this->cart->getId()]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);
        $arrayJson = json_decode($result->getContent(), 1);
        $this->assertEquals($arrayJson['statusType'], 'success');
        $this->assertEquals($arrayJson['message'], 'Data successfuly synchronized.');
        $this->assertEquals(
            $arrayJson['url'],
            $this->getUrl('orocrm_magento_order_view', ['id' => $this->order->getId()])
        );

        $this->client->request('GET', $this->getUrl('orocrm_magento_cart_view', ['id' => $this->cart->getId()]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->assertContains('Cart Information', $result->getContent());
        $this->assertContains(self::TEST_NEW_EMAIL, $result->getContent());
        $this->assertContains((string)self::TEST_NEW_ITEMS_QTY, $result->getContent());
        $this->assertContains((string)'Expired', $result->getContent());

        $this->assertContains('Customer Information', $result->getContent());
        $this->assertContains('test@example.com', $result->getContent());
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
            'payment'                     => [],
            'store_code'                  => $cart->getStore()->getCode(),
            'store_storename'             => $cart->getStore()->getName(),
            'store_website_id'            => $cart->getStore()->getWebsite()->getOriginId(),
            'store_website_code'          => $cart->getStore()->getWebsite()->getCode(),
            'store_website_name'          => $cart->getStore()->getWebsite()->getName(),
            'customer_group_code'         => 'General',
            'customer_group_name'         => 'General',
        ];
    }
}
