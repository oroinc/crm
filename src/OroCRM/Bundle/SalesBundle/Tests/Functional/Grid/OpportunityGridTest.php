<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Grid;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;
use OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OpportunityGridTest extends AbstractDatagridTestCase
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
            'Opportunity grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name'         => 'opname',
                        'channelName'  => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'budgetAmount' => 50.00,
                        'probability'  => 10,
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Opportunity grid with filter'    => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [
                        'sales-opportunity-grid[_filter][budgetAmount][value]' => 50.00,
                    ],
                    'assert'              => [
                        'name'         => 'opname',
                        'channelName'  => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'budgetAmount' => 50.00,
                        'probability'  => 10,
                    ],
                    'expectedResultCount' => 1
                ]
            ],
            'Opportunity grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [
                        'sales-opportunity-grid[_filter][budgetAmount][value]' => 150.00,
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ]
        ];
    }
}
