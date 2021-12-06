<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Grid;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;

class SalesFunnelGridTest extends AbstractDatagridTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadSalesBundleFixtures::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function gridProvider(): array
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
                        'opportunityName'        => 'opname',
                        'budgetAmount'           => 'USD50.0000',
                        'opportunityProbability' => 0.1
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
