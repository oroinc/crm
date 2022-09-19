<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;

class B2bCustomerControllerTest extends AbstractDatagridTestCase
{
    /** @var B2bCustomer */
    private static $customer;

    /** @var Account */
    private static $account;

    /** @var Channel */
    private static $channel;

    protected bool $isRealGridRequest = false;

    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadSalesBundleFixtures::class]);
    }

    protected function postFixtureLoad()
    {
        self::$account = $this->getReference('default_account');
        self::$customer = $this->getReference('default_b2bcustomer');
        self::$channel = $this->getReference('default_channel');
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_sales_b2bcustomer_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * {@inheritdoc}
     * @dataProvider gridProvider
     */
    public function testGrid(array $requestData)
    {
        parent::testGrid($requestData);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_sales_b2bcustomer_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();

        $form['oro_sales_b2bcustomer[name]'] = $name;
        $form['oro_sales_b2bcustomer[customer_association_account]'] = self::$account->getId();
        $form['oro_sales_b2bcustomer[dataChannel]'] = self::$channel->getId();
        $form['oro_sales_b2bcustomer[owner]']   = 1;

        $this->client->followRedirects();
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Customer saved', $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testUpdate(): array
    {
        $response = $this->client->requestGrid(
            'oro-sales-b2bcustomers-grid',
            [
                'oro-sales-b2bcustomers-grid[_filter][name][channelName]' => 'b2b Channel',
                'oro-sales-b2bcustomers-grid[_filter][name][value]' => null,
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $returnValue = $result;
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_b2bcustomer_update', ['id' => $result['id']])
        );

        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['oro_sales_b2bcustomer[name]'] = $name;
        $form['oro_sales_b2bcustomer[owner]']   = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString('Customer saved', $crawler->html());

        $returnValue['name'] = $name;

        return $returnValue;
    }

    /**
     * @depends testUpdate
     */
    public function testView(array $returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_b2bcustomer_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString($returnValue['name'], $crawler->html());
    }

    /**
     * @depends testUpdate
     */
    public function testInfo(array $returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_sales_b2bcustomer_widget_info',
                ['id' => $returnValue['id'], '_widgetContainer' => 'block']
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString($returnValue['name'], $crawler->html());
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(array $returnValue)
    {
        $this->ajaxRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_b2bcustomer', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_sales_b2bcustomer_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    /**
     * {@inheritdoc}
     */
    public function gridProvider(): array
    {
        return [
            'B2B Customer grid'              => [
                [
                    'gridParameters'      => [
                        'gridName' => 'oro-sales-b2bcustomers-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name'        => LoadSalesBundleFixtures::CUSTOMER_NAME,
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'B2B Customer grid with filter'  => [
                [
                    'gridParameters'      => [
                        'gridName' => 'oro-sales-b2bcustomers-grid'
                    ],
                    'gridFilters'         => [
                        'oro-sales-b2bcustomers-grid[_filter][name][value]' => 'b2bCustomer name',
                    ],
                    'assert'              => [
                        'name'        => LoadSalesBundleFixtures::CUSTOMER_NAME,
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'B2B Customer grid without data' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'oro-sales-b2bcustomers-grid'
                    ],
                    'gridFilters'         => [
                        'oro-sales-b2bcustomers-grid[_filter][name][value]' => 'some other type',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
