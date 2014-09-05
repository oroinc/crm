<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class CartControllerTest extends AbstractController
{
    /** @var \OroCRM\Bundle\MagentoBundle\Entity\Cart */
    public static $cart;

    protected function postFixtureLoad()
    {
        parent::postFixtureLoad();

        self::$cart = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:Cart')
            ->findOneByChannel(self::$integration);
    }

    protected function getMainEntityId()
    {
        return self::$cart->getid();
    }

    public function testView()
    {
        $this->client->request('GET', $this->getUrl('orocrm_magento_cart_view', ['id' => $this->getMainEntityId()]));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Cart Information', $result->getContent());
        $this->assertContains('email@email.com', $result->getContent());
        $this->assertContains('Customer Information', $result->getContent());
        $this->assertContains('test@example.com', $result->getContent());
        $this->assertContains('Cart Items', $result->getContent());
        $this->assertContains('Demo Web store', $result->getContent());
        $this->assertContains('Sync Data', $result->getContent());
        $this->assertContains('Open', $result->getContent());
        $this->assertContains('web site', $result->getContent());
        $this->assertContains('demo store', $result->getContent());
        $this->assertContains('Log call', $result->getContent());
        $this->assertContains('Send email', $result->getContent());
        $this->assertContains('Convert to opportunity', $result->getContent());

        $filteredHtml = str_replace(['<br/>', '<br />'], ' ', $result->getContent());
        $this->assertContains(
            'John Doe street CITY AZ US 123456',
            preg_replace('#\s+#', ' ', $filteredHtml)
        );
    }

    public function gridProvider()
    {
        return [
            'Magento cart grid'                             => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-cart-grid'
                    ],
                    'gridFilters'         => [],
                    'channelName'         => 'Magento channel',
                    'assert'              => [
                        'firstName'  => 'John',
                        'lastName'   => 'Doe',
                        'email'      => 'email@email.com',
                        'regionName' => 'Arizona'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Magento cart grid with filters'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-cart-grid'
                    ],
                    'gridFilters'         => [
                        'magento-cart-grid[_filter][lastName][value]'  => 'Doe',
                        'magento-cart-grid[_filter][firstName][value]' => 'John',
                    ],
                    'channelName'         => 'Magento channel',
                    'assert'              => [
                        'firstName'  => 'John',
                        'lastName'   => 'Doe',
                        'email'      => 'email@email.com',
                        'regionName' => 'Arizona'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Magento cart grid with filters without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-cart-grid'
                    ],
                    'gridFilters'         => [
                        'magento-cart-grid[_filter][lastName][value]'  => 'Doe',
                        'magento-cart-grid[_filter][firstName][value]' => 'Doe',
                    ],
                    'channelName'         => 'Magento channel',
                    'assert'              => [],
                    'expectedResultCount' => 0
                ]
            ],
            'Cart item grid'                                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'magento-cartitem-grid',
                        'id'       => 'id',
                    ],
                    'gridFilters'         => [],
                    'channelName'         => 'Demo Web store',
                    'assert'              => [
                        'sku'            => 'sku',
                        'qty'            => 0,
                        'rowTotal'       => '$100.00',
                        'taxAmount'      => '$10.00',
                        'discountAmount' => '$0.00'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
        ];
    }
}
