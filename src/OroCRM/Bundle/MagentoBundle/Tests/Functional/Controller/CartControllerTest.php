<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;


/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class CartControllerTest extends AbstractController
{
    public function gridProvider()
    {
        return [
            [
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
                    'oneOrMore'      => true
                ],
            ],
            [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cart-grid'
                    ],
                    'gridFilters'    => [
                        'magento-cart-grid[_filter][lastName][value]'  => 'Doe',
                        'magento-cart-grid[_filter][firstName][value]' => 'John',
                    ],
                    'channelName'    => 'Demo Web store',
                    'verifying'      => [
                        'firstName'  => 'John',
                        'lastName'   => 'Doe',
                        'email'      => 'email@email.com',
                        'regionName' => 'Arizona'
                    ],
                    'oneOrMore'      => true
                ],
            ],
            [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-cart-grid'
                    ],
                    'gridFilters'    => [
                        'magento-cart-grid[_filter][lastName][value]'  => 'Doe',
                        'magento-cart-grid[_filter][firstName][value]' => 'Doe',
                    ],
                    'channelName'    => 'Demo Web store',
                    'verifying'      => [
                        'firstName'  => 'John',
                        'lastName'   => 'Doe',
                        'email'      => 'email@email.com',
                        'regionName' => 'Arizona'
                    ],
                    'oneOrMore'      => false
                ]
            ]
        ];
    }
}
