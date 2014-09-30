<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Grid;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

use OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class LeadGridTest extends AbstractDatagridTestCase
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
            'Lead grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-lead-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name'        => 'Lead name',
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'firstName'   => 'fname',
                        'lastName'    => 'lname',
                        'email'       => 'email@email.com'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Lead grid with filters'   => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-lead-grid'
                    ],
                    'gridFilters'         => [
                        'sales-lead-grid[_filter][name][value]' => 'Lead name',
                    ],
                    'assert'              => [
                        'name'        => 'Lead name',
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'firstName'   => 'fname',
                        'lastName'    => 'lname',
                        'email'       => 'email@email.com'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Lead grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-lead-grid'
                    ],
                    'gridFilters'         => [
                        'sales-lead-grid[_filter][name][value]' => 'some name',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
