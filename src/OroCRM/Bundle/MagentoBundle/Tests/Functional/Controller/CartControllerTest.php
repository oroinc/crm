<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

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

        self::$cart = $this->getReference('cart');
    }

    /**
     * @afterClass
     */
    public function clear()
    {
        self::$cart = null;
        gc_collect_cycles();
    }

    /**
     * @return int
     */
    protected function getMainEntityId()
    {
        $this->assertNotEmpty(self::$cart);

        return self::$cart->getId();
    }

    public function testView()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_magento_cart_view',
                ['id' => $this->getMainEntityId(), 'isRemoved' => 0]
            )
        );
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
        $this->assertContains('Send email', $result->getContent());

        $filteredHtml = str_replace(['<br/>', '<br />'], ' ', $result->getContent());

        /** @var Organization $organization */
        $organization = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();

        $this->assertContains(
            'John Doe ' . $organization->getName() . ' street CITY AZ US 123456',
            preg_replace('#\s+#', ' ', $filteredHtml)
        );
    }

    public function gridProvider()
    {
        return [
            'Magento cart grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cart-grid'
                    ],
                    'gridFilters' => [],
                    'assert' => [
                        'channelName' => 'Magento channel',
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'email@email.com',
                        'regionName' => 'Arizona'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Magento cart grid with filters' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cart-grid'
                    ],
                    'gridFilters' => [
                        'magento-cart-grid[_filter][lastName][value]' => 'Doe',
                        'magento-cart-grid[_filter][firstName][value]' => 'John'
                    ],
                    'assert' => [
                        'channelName' => 'Magento channel',
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'email@email.com',
                        'regionName' => 'Arizona'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Magento cart grid with filters without result' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cart-grid'
                    ],
                    'gridFilters' => [
                        'magento-cart-grid[_filter][lastName][value]' => 'Doe',
                        'magento-cart-grid[_filter][firstName][value]' => 'Doe'
                    ],
                    'assert' => [],
                    'expectedResultCount' => 0
                ]
            ],
            'Cart item grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cartitem-active-grid',
                        'id' => 'id',
                    ],
                    'gridFilters' => [],
                    'assert' => [
                        'sku' => 'sku',
                        'qty' => 0,
                        'rowTotal' => '$100.00',
                        'taxAmount' => '$10.00',
                        'discountAmount' => '$0.00'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Cart item grid removed' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cartitem-removed-grid',
                        'id' => 'id',
                    ],
                    'gridFilters' => [],
                    'assert' => [],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
