<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

class CartControllerTest extends AbstractController
{
    /**
     * @return int
     */
    protected function getMainEntityId()
    {
        $this->assertNotEmpty($this->getReference('cart'));

        return $this->getReference('cart')->getId();
    }

    public function testView()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_magento_cart_view',
                ['id' => $this->getMainEntityId(), 'isRemoved' => 0]
            )
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('Cart Information', $result->getContent());
        static::assertStringContainsString('email@email.com', $result->getContent());
        static::assertStringContainsString('Customer Information', $result->getContent());
        static::assertStringContainsString('test@example.com', $result->getContent());
        static::assertStringContainsString('Cart Items', $result->getContent());
        static::assertStringContainsString('Demo Web store', $result->getContent());
        static::assertStringContainsString('Sync Data', $result->getContent());
        static::assertStringContainsString('Open', $result->getContent());
        static::assertStringContainsString('web site', $result->getContent());
        static::assertStringContainsString('demo store', $result->getContent());

        $filteredHtml = \str_replace(['<br/>', '<br />'], ' ', $result->getContent());

        /** @var Organization $organization */
        $organization = $this->client
            ->getContainer()
            ->get('doctrine')
            ->getRepository('OroOrganizationBundle:Organization')
            ->getFirst();

        static::assertStringContainsString(
            'John Doe ' . $organization->getName() . ' street CITY AZ US 123456',
            \preg_replace('#\s+#', ' ', $filteredHtml)
        );
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'Magento cart grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cart-grid',
                        'magento-cart-grid[_sort_by][originId]' => 'ASC',
                    ],
                    'gridFilters' => [],
                    'asserts' => [
                        [
                            'channelName' => 'Magento channel',
                            'firstName' => 'John',
                            'lastName' => 'Doe',
                            'email' => 'email@email.com',
                            'regionName' => 'Arizona'
                        ],
                        [
                            'channelName' => 'Magento channel',
                            'firstName' => 'Guest Jack',
                            'lastName' => 'Guest White',
                            'email' => 'guest@email.com',
                            'regionName' => 'Arizona'
                        ]
                    ],
                    'expectedResultCount' => 2
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
