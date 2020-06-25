<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller;

class CustomerControllerTest extends AbstractController
{
    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        parent::setUp();
    }

    /** @return int */
    protected function getMainEntityId()
    {
        return $this->getReference('customer')->getId();
    }

    public function testView()
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_magento_customer_view', ['id' => $this->getMainEntityId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('Customers', $result->getContent());
        static::assertStringContainsString('test@example.com', $result->getContent());
        static::assertStringContainsString('John', $result->getContent());
        static::assertStringContainsString('Doe', $result->getContent());
        static::assertStringContainsString('John Doe', $result->getContent());
        static::assertStringContainsString('Address Book', $result->getContent());
        static::assertStringContainsString('Sales', $result->getContent());
        static::assertStringContainsString('Orders', $result->getContent());
        static::assertStringContainsString('Shopping Carts', $result->getContent());
        static::assertStringContainsString('Demo Web store', $result->getContent());
        static::assertStringContainsString('web site', $result->getContent());
    }

    /**
     * Moved here to fix order of executed tests, because create and update tests work with data same to fixture.
     *
     * @dataProvider gridProvider
     * @param array $requestData
     */
    public function testGrid($requestData)
    {
        parent::testGrid($requestData);
    }

    /**
     * @depends testGrid
     */
    public function testCreate()
    {
        $this->client->request('GET', $this->getUrl('oro_magento_customer_create'));
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 200);
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'magento-customers-grid',
            ['magento-customers-grid[_filter][email][value]' => 'test@example.com']
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $this->client->request(
            'GET',
            $this->getUrl('oro_magento_customer_update', ['id' => $result['id']])
        );
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 200);
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'Customers grid' => [
                [
                    'gridParameters' => ['gridName' => 'magento-customers-grid'],
                    'gridFilters' => [],
                    'assert' => [
                        'channelName' => 'Magento channel',
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'test@example.com',
                        'lifetime' => '$0.00',
                        'countryName' => 'United States',
                        'regionName' => 'Arizona',
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Customers grid with filters' => [
                [
                    'gridParameters' => ['gridName' => 'magento-customers-grid'],
                    'gridFilters' => [
                        'magento-customers-grid[_filter][lastName][value]' => 'Doe',
                        'magento-customers-grid[_filter][firstName][value]' => 'John',
                        'magento-customers-grid[_filter][email][value]' => 'test@example.com',
                    ],
                    'assert' => [
                        'channelName' => 'Magento channel',
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'test@example.com',
                        'lifetime' => '$0.00',
                        'countryName' => 'United States',
                        'regionName' => 'Arizona',
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Customers grid with filters without result' => [
                [
                    'gridParameters' => ['gridName' => 'magento-customers-grid'],
                    'gridFilters' => [
                        'magento-customers-grid[_filter][lastName][value]' => 'Doe1',
                        'magento-customers-grid[_filter][firstName][value]' => 'John1',
                        'magento-customers-grid[_filter][email][value]' => 'test@example.com',
                    ],
                    'assert' => [],
                    'expectedResultCount' => 0
                ],
            ],
            'Customer Cart grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-customer-cart-widget-grid',
                        'id' => 'customerId',
                        'channel' => 'channelId'
                    ],
                    'gridFilters' => [],
                    'assert' => [
                        'grandTotal' => '$2.54',
                        'statusLabel' => 'Open',
                        'workflowStepLabel' => 'Open',
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Customer order grid' => [
                [
                    'gridParameters' => [
                        'gridName' => 'magento-customer-order-grid',
                        'id' => 'customerId',
                        'channel' => 'channelId'
                    ],
                    'gridFilters' => [],
                    'assert' => [
                        'totalAmount' => '$0.00',
                        'totalPaidAmount' => '$17.85',
                        'status' => 'open',
                        'workflowStepLabel' => 'Not contacted',
                    ],
                    'expectedResultCount' => 1
                ],
            ],
        ];
    }
}
