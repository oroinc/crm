<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Grid;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

use OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class B2bCustomerGridTest extends AbstractDatagridTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures(['OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'B2B Customer grid'              => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orocrm-sales-b2bcustomers-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name'        => LoadSalesBundleFixtures::CUSTOMER_NAME,
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'B2B Customer grid with filter'  => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orocrm-sales-b2bcustomers-grid'
                    ],
                    'gridFilters'         => [
                        'orocrm-sales-b2bcustomers-grid[_filter][name][value]' => 'b2bCustomer name',
                    ],
                    'assert'              => [
                        'name'        => LoadSalesBundleFixtures::CUSTOMER_NAME,
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'B2B Customer grid without data' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orocrm-sales-b2bcustomers-grid'
                    ],
                    'gridFilters'         => [
                        'orocrm-sales-b2bcustomers-grid[_filter][name][value]' => 'some other type',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
