<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Functional\DatagridReport;

use Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures\LoadLeadsData;
use Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures\LoadLeadSourceData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class LeadsByDateReportTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures(
            [
                LoadLeadSourceData::class,
                LoadLeadsData::class,
            ]
        );
    }

    public function testDatagridReport()
    {
        $currentDate = new \DateTime('now');
        $currentDate = $currentDate->format('Y-m-d');

        $response = $this->client->requestGrid(
            'oro_reportcrm-leads-by_date',
            [
                'oro_reportcrm-leads-by_date[_filter][createdDate][type]' => 1,
                'oro_reportcrm-leads-by_date[_filter][createdDate][value][start]' => $currentDate,
                'oro_reportcrm-leads-by_date[_filter][createdDate][value][end]' => $currentDate,
                'oro_reportcrm-leads-by_date[_filter][leadsCount][type]' => 7,
                'oro_reportcrm-leads-by_date[_filter][leadsCount][value]' => 2,
                'oro_reportcrm-leads-by_date[_filter][leadsCount][value_end]' => 2,
            ],
            true
        );
        $result = self::getJsonResponseContent($response, 200);

        $valuableResultData = $this->getValuableDataFromResult($result, ['createdDate', 'leadsCount']);

        $this->assertEquals(
            [
                'rows' => [
                    [
                        'createdDate' => $currentDate,
                        'leadsCount' => 2,
                    ]
                ],
                'totals' => [
                    'createdDate' => 'Grand Total',
                    'leadsCount' => 2,
                ]
            ],
            $valuableResultData
        );
    }

    private function getValuableDataFromResult(array $response, array $keys): array
    {
        return [
            'rows' => array_map(
                function ($item) use ($keys) {
                    return array_intersect_key($item, array_flip($keys));
                },
                $response['data']
            ),
            'totals' => array_map(
                function ($item) {
                    return $item['total'] ?? $item['label'];
                },
                array_intersect_key($response['options']['totals']['grand_total']['columns'], array_flip($keys))
            )
        ];
    }
}
