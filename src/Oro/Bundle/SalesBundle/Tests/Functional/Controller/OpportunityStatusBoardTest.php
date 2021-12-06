<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadOpportunityStatusBoardFixtures;

class OpportunityStatusBoardTest extends AbstractDatagridTestCase
{
    protected function setUp(): void
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadOpportunityStatusBoardFixtures::class]);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_sales_opportunity_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * Check that status board loads correct records count based on available statuses
     * {@inheritdoc}
     */
    public function gridProvider(): array
    {
        return [
            'Opportunity grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid',
                        'sales-opportunity-grid[_pager][_per_page]' => 10,
                    ],
                    'gridFilters'         => [],
                    'assert'              => [],
                    'expectedResultCount' => 10
                ],
            ],
            'Opportunity status board' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid',
                        'sales-opportunity-grid[_appearance][_type]' => 'board',
                        'sales-opportunity-grid[_appearance][_data][id]' => 'opportunity-by-status',
                        'sales-opportunity-grid[_pager][_per_page]' => 25,
                    ],
                    'gridFilters'         => [],
                    'assert'              => [],
                    'expectedResultCount' => 25
                ],
            ],
        ];
    }
}
