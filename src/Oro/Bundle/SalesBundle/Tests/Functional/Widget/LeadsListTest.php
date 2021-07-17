<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadLeadsListWidgetFixture;
use Symfony\Component\DomCrawler\Crawler;

class LeadsListTest extends BaseStatistics
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadLeadsListWidgetFixture::class
        ]);
    }

    public function testDefaultConfiguration()
    {
        $this->getConfigureDialog();

        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Save')->form();
        $this->client->submit($form);

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in submit widget configuration options!');

        $crawler = $this->getDashboardWidget();

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in getting widget view!');
        $this->assertNotEmpty($crawler->html());
    }

    /**
     * @dataProvider widgetDataProvider
     *
     * @param string|array $excludedStatuses
     * @param string $property
     * @param string $order
     * @param array $expectedResults
     */
    public function testCustomConfiguration(
        $excludedStatuses,
        string $property,
        string $order,
        array $expectedResults
    ) {
        $this->getConfigureDialog();

        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Save')->form();
        $form['leads_list[owners][users]'] = '1';
        $form['leads_list[excluded_statuses]'] = $excludedStatuses;
        $form['leads_list[sortBy][property]'] = $property;
        $form['leads_list[sortBy][order]'] = $order;

        $this->client->submit($form);

        $this->assertResults($expectedResults);
    }

    public function widgetDataProvider(): array
    {
        return [
            [
                'excludedStatuses' => 'qualified',
                'property' => 'firstName',
                'order' => 'ASC',
                'expectedResults' => [
                    'owner' => 'John Doe',
                    'excluded_statuses' => 'Qualified',
                    'sort_by' => 'First name Ascending'
                ],
            ],
            [
                'excludedStatuses' => 'new',
                'property' => 'firstName',
                'order' => 'DESC',
                'expectedResults' => [
                    'owner' => 'John Doe',
                    'excluded_statuses' => 'New',
                    'sort_by' => 'First name Descending'
                ],
            ],
            [
                'excludedStatuses' => ['canceled'],
                'property' => 'lastName',
                'order' => 'ASC',
                'expectedResults' => [
                    'owner' => 'John Doe',
                    'excluded_statuses' => 'Disqualified',
                    'sort_by' => 'Last name Ascending'
                ],
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getWidget(): Widget
    {
        return $this->getReference('widget_leads_list');
    }

    private function assertResults(array $expectedResults)
    {
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in submit widget configuration options!');

        $crawler = $this->getDashboardWidget();

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in getting widget view!');
        $this->assertNotEmpty($crawler->html());

        static::assertStringContainsString($expectedResults['owner'], $response->getContent());
        static::assertStringContainsString($expectedResults['excluded_statuses'], $response->getContent());
        static::assertStringContainsString($expectedResults['sort_by'], $response->getContent());
    }

    /**
     * @return null|Crawler
     */
    private function getDashboardWidget()
    {
        return $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_widget',
                [
                    'widget' => 'leads_list',
                    'bundle' => 'OroSalesBundle',
                    'name' => 'leadsList',
                    '_widgetId' => $this->getWidget()->getId()
                ]
            )
        );
    }
}
