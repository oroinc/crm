<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OrderControllerTest extends AbstractController
{
    /** @var \Oro\Bundle\MagentoBundle\Entity\Order */
    public static $order;

    protected function postFixtureLoad()
    {
        parent::postFixtureLoad();

        self::$order = $this->getReference('order');
    }

    protected function getMainEntityId()
    {
        return self::$order->getid();
    }

    public function testView()
    {
        $this->client->request('GET', $this->getUrl('oro_magento_order_view', ['id' => $this->getMainEntityId()]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Orders', $result->getContent());
        $this->assertContains('General Information', $result->getContent());
        $this->assertContains('Order items', $result->getContent());
        $this->assertContains('Activity', $result->getContent());
        $this->assertContains('Send email', $result->getContent());
        $this->assertContains('Sync Data', $result->getContent());
        $this->assertContains('USD 4.40', $result->getContent());
        $this->assertContains('open', $result->getContent());
        $this->assertContains('customer@email.com', $result->getContent());
        $this->assertContains('USD 12.47', $result->getContent());
        $this->assertContains('USD 5.00', $result->getContent());
        $this->assertContains('USD 17.85', $result->getContent());
        $this->assertContains('USD 11.00', $result->getContent());
        $this->assertContains('USD 4.00', $result->getContent());
        $this->assertContains('USD 0.00', $result->getContent());
        $this->assertContains('Some unique shipping method', $result->getContent());
        $this->assertContains('127.0.0.1', $result->getContent());
        $this->assertContains('some very unique gift message', $result->getContent());
        $this->assertContains('web site', $result->getContent());
        $this->assertContains('Demo Web store', $result->getContent());
        $this->assertContains('John Doe', $result->getContent());
        $this->assertContains('Shopping Cart', $result->getContent());
    }

    public function gridProvider()
    {
        return [
            'Magento order grid'                             => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-order-grid'
                    ],
                    'gridFilters'         => [],
                    'asserts' => [
                        [
                            'channelName' => 'Magento channel',
                            'firstName'   => 'John',
                            'lastName'    => 'Doe',
                            'status'      => 'open',
                            'subTotal'    => 'USD 0.00',
                        ],
                        [
                            'channelName' => 'Magento channel',
                            'firstName'   => 'Guest Jack',
                            'lastName'    => 'Guest White',
                            'status'      => 'open',
                            'subTotal'    => 'USD 0.00',
                        ]
                    ],
                    'expectedResultCount' => 2
                ],
            ],
            'Magento order grid with filters'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-order-grid'
                    ],
                    'gridFilters'         => [
                        'magento-order-grid[_filter][lastName][value]'  => 'Doe',
                        'magento-order-grid[_filter][firstName][value]' => 'John',
                        'magento-order-grid[_filter][status][value]'    => 'open',
                    ],
                    'assert'              => [
                        'channelName' => 'Magento channel',
                        'firstName'   => 'John',
                        'lastName'    => 'Doe',
                        'status'      => 'open',
                        'subTotal'    => 'USD 0.00',
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Magento order grid with filters without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-order-grid'
                    ],
                    'gridFilters'         => [
                        'magento-order-grid[_filter][lastName][value]'  => 'Doe',
                        'magento-order-grid[_filter][firstName][value]' => 'John',
                        'magento-order-grid[_filter][status][value]'    => 'close',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
            'Magento order item grid'                        => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-orderitem-grid',
                        'id'       => 'id',
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'sku'            => 'some sku',
                        'qty'            => 1,
                        'rowTotal'       => 'USD 234.00',
                        'taxAmount'      => 'USD 1.50',
                        'discountAmount' => 'USD 0.00'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
        ];
    }
}
