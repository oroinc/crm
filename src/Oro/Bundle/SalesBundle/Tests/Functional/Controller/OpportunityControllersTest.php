<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Symfony\Component\DomCrawler\Form;

class OpportunityControllersTest extends AbstractDatagridTestCase
{
    /** @var B2bCustomer */
    protected static $customer;

    /** @var Account */
    protected static $account;

    /** @var bool */
    protected $isRealGridRequest = false;

    protected function setUp(): void
    {
        $this->initClient(
            ['debug' => false],
            $this->generateBasicAuthHeader()
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);
    }

    protected function postFixtureLoad()
    {
        self::$customer = $this->getReference('default_b2bcustomer');
        self::$account = $this->getReference('default_account');
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_sales_opportunity_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreateWithCustomer()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_sales_opportunity_customer_aware_create',
                [
                    'targetClass' => 'Oro_Bundle_AccountBundle_Entity_Account',
                    'targetId' => self::$account->getId(),
                ]
            )
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_sales_opportunity_form[name]'] = 'opname';
        $form['oro_sales_opportunity_form[probability]'] = 10;
        $form['oro_sales_opportunity_form[budgetAmount][value]'] = 50;
        $form['oro_sales_opportunity_form[budgetAmount][currency]'] = 'USD';
        $form['oro_sales_opportunity_form[owner]'] = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Opportunity saved", $crawler->html());
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_sales_opportunity_create'));

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['oro_sales_opportunity_form[name]']         = $name;
        $form['oro_sales_opportunity_form[probability]']  = 50;
        $form['oro_sales_opportunity_form[budgetAmount][value]'] = 10000;
        $form['oro_sales_opportunity_form[budgetAmount][currency]'] = 'USD';
        $form['oro_sales_opportunity_form[customerNeed]'] = 10001;
        $form['oro_sales_opportunity_form[closeReason]']  = 'cancelled';
        $form['oro_sales_opportunity_form[owner]']        = 1;
        $form['oro_sales_opportunity_form[customerAssociation]'] = '{"value":"Account"}'; //create with new Account

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Opportunity saved", $crawler->html());

        return $name;
    }

    /**
    * @param string $name
    * @depends testCreate
    *
    * @return string
    */
    public function testUpdate($name)
    {
        $response = $this->client->requestGrid(
            'sales-opportunity-grid',
            [
                'sales-opportunity-grid[_filter][name][type]' => '1',
                'sales-opportunity-grid[_filter][name][value]' => $name,
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $returnValue = $result;
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_update', ['id' => $result['id']])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['oro_sales_opportunity_form[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Opportunity saved", $crawler->html());

        $returnValue['name'] = $name;

        return $returnValue;
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testView(array $returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("{$returnValue['name']} - Opportunities - Sales", $crawler->html());
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testInfo(array $returnValue)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_sales_opportunity_info',
                ['id' => $returnValue['id'], '_widgetContainer' => 'block']
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @depends testUpdate
     */
    public function testDelete(array $returnValue)
    {
        $this->ajaxRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_opportunity', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_view', ['id' => $returnValue['id']])
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 404);
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'Opportunity grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name'         => 'opname',
                        'budgetAmount' => 'USD50.0000',
                        'probability'  => 0.1,
                    ],
                    'expectedResultCount' => 2
                ],
            ],
            'Opportunity grid with filter'    => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [
                        'sales-opportunity-grid[_filter][budgetAmountValue][value]' => '50.00',
                        'sales-opportunity-grid[_filter][budgetAmountValue][type]' => '3',
                    ],
                    'assert'              => [
                        'name'              => 'opname',
                        'budgetAmount'      => 'USD50.0000',
                        'probability'       => 0.1,
                    ],
                    'expectedResultCount' => 2
                ]
            ],
            'Opportunity grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [
                        'sales-opportunity-grid[_filter][budgetAmount][value]' => '150.00',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ]
        ];
    }
}
