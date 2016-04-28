<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Controller;

use OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;
use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class B2bCustomerControllerTest extends AbstractDatagridTestCase
{
    /** @var B2bCustomer */
    protected static $customer;

    /** @var Account */
    protected static $account;

    /** @var Channel */
    protected static $channel;

    /** @var bool */
    protected $isRealGridRequest = false;

    protected function setUp()
    {
        $this->initClient(
            [],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);
    }

    protected function postFixtureLoad()
    {
        self::$account  = $this->getReference('default_account');
        self::$customer = $this->getReference('default_b2bcustomer');
        self::$channel  = $this->getReference('default_channel');
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('orocrm_sales_b2bcustomer_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * {@inheritdoc}
     * @dataProvider gridProvider
     */
    public function testGrid($requestData)
    {
        parent::testGrid($requestData);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('orocrm_sales_b2bcustomer_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();

        $form['orocrm_sales_b2bcustomer_form[name]'] = $name;
        $form['orocrm_sales_b2bcustomer_form[account]'] = self::$account->getId();
        $form['orocrm_sales_b2bcustomer_form[dataChannel]'] = self::$channel->getId();
        $form['orocrm_sales_b2bcustomer_form[owner]']   = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Customer saved", $crawler->html());
    }

    /**
     * @param string $name
     *
     * @depends testCreate
     *
     * @return string
     */
    public function testUpdate($name)
    {
        $response = $this->client->requestGrid(
            'orocrm-sales-b2bcustomers-grid',
            [
                'orocrm-sales-b2bcustomers-grid[_filter][name][channelName]' => 'b2b Channel',
                'orocrm-sales-b2bcustomers-grid[_filter][name][value]' => $name,
            ]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $returnValue = $result;
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_b2bcustomer_update', ['id' => $result['id']])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['orocrm_sales_b2bcustomer_form[name]'] = $name;
        $form['orocrm_sales_b2bcustomer_form[owner]']   = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Customer saved", $crawler->html());

        $returnValue['name'] = $name;

        return $returnValue;
    }

    /**
     * @param array $returnValue
     *
     * @depends testUpdate
     *
     * @return string
     */
    public function testView($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_b2bcustomer_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains($returnValue['name'], $crawler->html());
    }

    /**
     * @param array $returnValue
     *
     * @depends testUpdate
     *
     * @return string
     */
    public function testInfo($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_sales_b2bcustomer_widget_info',
                ['id' => $returnValue['id'], '_widgetContainer' => 'block']
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains($returnValue['name'], $crawler->html());
    }

    /**
     * @param array $returnValue
     *
     * @depends testUpdate
     */
    public function testDelete($returnValue)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_b2bcustomer', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_b2bcustomer_view', ['id' => $returnValue['id']])
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
            'B2B Customer grid'              => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orocrm-sales-b2bcustomers-grid'
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
                        'gridName' => 'orocrm-sales-b2bcustomers-grid'
                    ],
                    'gridFilters'         => [
                        'orocrm-sales-b2bcustomers-grid[_filter][name][value]' => 'b2bCustomer name',
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
                        'gridName' => 'orocrm-sales-b2bcustomers-grid'
                    ],
                    'gridFilters'         => [
                        'orocrm-sales-b2bcustomers-grid[_filter][name][value]' => 'some other type',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
