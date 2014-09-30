<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Grid;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

use OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class SalesFunnelGridTest extends AbstractDatagridTestCase
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
            'Sales funnel grid by lead'                       => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-funnel-grid'
                    ],
                    'gridFilters'         => [
                        'sales-funnel-grid[_filter][leadName][value]' => 'Lead name',
                    ],
                    'assert'              => [
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'leadName'    => 'Lead name',
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Sales funnel grid by lead without result'        => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-funnel-grid'
                    ],
                    'gridFilters'         => [
                        'sales-funnel-grid[_filter][leadName][value]' => 'something',
                    ],
                    'assert'              => [
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'leadName'    => 'Lead name',
                    ],
                    'expectedResultCount' => 0
                ],
            ],
            'Sales funnel grid by opportunity'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-funnel-grid'
                    ],
                    'gridFilters'         => [
                        'sales-funnel-grid[_filter][opportunityName][value]' => 'opname',
                    ],
                    'assert'              => [
                        'channelName'            => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'opportunityName'        => 'opname',
                        'opportunityBudget'      => 50.00,
                        'opportunityProbability' => 10
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Sales funnel grid by opportunity without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-funnel-grid'
                    ],
                    'gridFilters'         => [
                        'sales-funnel-grid[_filter][opportunityName][value]' => 'something',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
