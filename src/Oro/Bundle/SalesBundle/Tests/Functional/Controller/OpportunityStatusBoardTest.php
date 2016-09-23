<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadOpportunityStatusBoardFixtures;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OpportunityStatusBoardTest extends AbstractDatagridTestCase
{
    /** @var bool */
    protected $isRealGridRequest = true;

    protected function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadOpportunityStatusBoardFixtures']);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_sales_opportunity_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * Check that status board loads correct records count based on available statuses
     *
     * @return array
     */
    public function gridProvider()
    {
        $perPage = 4;
        return [
            'Opportunity grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid',
                        'sales-opportunity-grid[_pager][_per_page]' => $perPage,
                    ],
                    'gridFilters'         => [],
                    'assert'              => [],
                    'expectedResultCount' => $perPage
                ],
            ],
            'Opportunity status board' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid',
                        'sales-opportunity-grid[_appearance][_type]' => 'board',
                        'sales-opportunity-grid[_appearance][_data][id]' => 'opportunity-by-status',
                        'sales-opportunity-grid[_pager][_per_page]' => $perPage,
                    ],
                    'gridFilters'         => [],
                    'assert'              => [],
                    'expectedResultCount' => LoadOpportunityStatusBoardFixtures::STATUSES_COUNT * $perPage
                ],
            ],
        ];
    }
}
