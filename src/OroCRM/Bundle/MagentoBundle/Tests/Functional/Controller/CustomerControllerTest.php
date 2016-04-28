<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class CustomerControllerTest extends AbstractController
{
    /** @var \OroCRM\Bundle\MagentoBundle\Entity\Customer */
    public static $customer;

    protected function postFixtureLoad()
    {
        parent::postFixtureLoad();

        self::$customer = $this->getReference('customer');
    }

    /** @return int */
    protected function getMainEntityId()
    {
        return self::$customer->getid();
    }

    public function testView()
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_magento_customer_view', ['id' => $this->getMainEntityId()])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains('Customers', $result->getContent());
        $this->assertContains('test@example.com', $result->getContent());
        $this->assertContains('John', $result->getContent());
        $this->assertContains('Doe', $result->getContent());
        $this->assertContains('John Doe', $result->getContent());
        $this->assertContains('Address Book', $result->getContent());
        $this->assertContains('Sales', $result->getContent());
        $this->assertContains('Orders', $result->getContent());
        $this->assertContains('Shopping Carts', $result->getContent());
        $this->assertContains('Demo Web store', $result->getContent());
        $this->assertContains('web site', $result->getContent());
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
     * @depend1s testGrid
     */
    public function testCreate()
    {
        $this->client->request('GET', $this->getUrl('orocrm_magento_customer_create'));
        $this->assertResponseStatusCodeEquals($this->client->getResponse(), 200);
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'magento-customers-grid',
            ['magento-customers-grid[_filter][email][value]' => 'john@example.com']
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_magento_customer_update', ['id' => $result['id']])
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
                        'stepLabel' => 'Open',
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
                        'stepLabel' => 'Not contacted',
                    ],
                    'expectedResultCount' => 1
                ],
            ],
        ];
    }
}
