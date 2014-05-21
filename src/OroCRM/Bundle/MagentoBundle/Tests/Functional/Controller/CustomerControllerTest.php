<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class CustomerControllerTest extends AbstractController
{
    public function gridProvider()
    {
        return [
            [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-customers-grid'
                    ],
                    'gridFilters'    => [],
                    'channelName'    => 'Demo Web store',
                    'verifying'      => [
                        'firstName'   => 'John',
                        'lastName'    => 'Doe',
                        'email'       => 'test@example.com',
                        'lifetime'    => '$0.00',
                        'countryName' => null,
                        'regionName'  => null,
                    ],
                    'oneOrMore'      => true
                ],
            ],
            [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-customers-grid'
                    ],
                    'gridFilters'    => [
                        'magento-customers-grid[_filter][lastName][value]'  => 'Doe',
                        'magento-customers-grid[_filter][firstName][value]' => 'John',
                        'magento-customers-grid[_filter][email][value]'     => 'test@example.com',
                    ],
                    'channelName'    => 'Demo Web store',
                    'verifying'      => [
                        'firstName'   => 'John',
                        'lastName'    => 'Doe',
                        'email'       => 'test@example.com',
                        'lifetime'    => '$0.00',
                        'countryName' => null,
                        'regionName'  => null,
                    ],
                    'oneOrMore'      => true
                ],
            ],
            [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-customers-grid'
                    ],
                    'gridFilters'    => [
                        'magento-customers-grid[_filter][lastName][value]'  => 'Doe1',
                        'magento-customers-grid[_filter][firstName][value]' => 'John1',
                        'magento-customers-grid[_filter][email][value]'     => 'test@example.com',
                    ],
                    'channelName'    => 'Demo Web store',
                    'verifying'      => [
                        'firstName'   => 'John',
                        'lastName'    => 'Doe',
                        'email'       => 'test@example.com',
                        'lifetime'    => '$0.00',
                        'countryName' => null,
                        'regionName'  => null,
                    ],
                    'oneOrMore'      => false
                ],
            ],
        ];
    }
}
