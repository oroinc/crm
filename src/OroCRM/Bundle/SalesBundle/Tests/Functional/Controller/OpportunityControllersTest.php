<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OpportunityControllersTest extends AbstractDatagridTestCase
{
    /** @var B2bCustomer */
    protected static $customer;

    /** @var  Channel */
    protected static $dataChannel;

    /** @var bool */
    protected $isRealGridRequest = false;

    protected function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);
    }

    protected function postFixtureLoad()
    {
        self::$customer = $this->getReference('default_b2bcustomer');
        self::$dataChannel = $this->getReference('default_channel');
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('orocrm_sales_opportunity_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('orocrm_sales_opportunity_create'));

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['orocrm_sales_opportunity_form[name]']         = $name;
        $form['orocrm_sales_opportunity_form[customer]']     = self::$customer->getId();
        $form['orocrm_sales_opportunity_form[probability]']  = 50;
        $form['orocrm_sales_opportunity_form[budgetAmount]'] = 10000;
        $form['orocrm_sales_opportunity_form[customerNeed]'] = 10001;
        $form['orocrm_sales_opportunity_form[closeReason]']  = 'cancelled';
        $form['orocrm_sales_opportunity_form[owner]']        = 1;
        $form['orocrm_sales_opportunity_form[dataChannel]']  = $this->getReference('default_channel')->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Opportunity saved", $crawler->html());

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
            $this->getUrl('orocrm_sales_opportunity_update', ['id' => $result['id']])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['orocrm_sales_opportunity_form[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Opportunity saved", $crawler->html());

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
            $this->getUrl('orocrm_sales_opportunity_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("{$returnValue['name']} - Opportunities - Sales", $crawler->html());
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
                'orocrm_sales_opportunity_info',
                ['id' => $returnValue['id'], '_widgetContainer' => 'block']
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     */
    public function testDelete(array $returnValue)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_opportunity', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_opportunity_view', ['id' => $returnValue['id']])
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
                        'channelName'  => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'budgetAmount' => 50.00,
                        'probability'  => 10,
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Opportunity grid with filter'    => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [
                        'sales-opportunity-grid[_filter][budgetAmount][value]' => 50.00,
                    ],
                    'assert'              => [
                        'name'         => 'opname',
                        'channelName'  => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'budgetAmount' => 50.00,
                        'probability'  => 10,
                    ],
                    'expectedResultCount' => 1
                ]
            ],
            'Opportunity grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-opportunity-grid'
                    ],
                    'gridFilters'         => [
                        'sales-opportunity-grid[_filter][budgetAmount][value]' => 150.00,
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ]
        ];
    }
}
