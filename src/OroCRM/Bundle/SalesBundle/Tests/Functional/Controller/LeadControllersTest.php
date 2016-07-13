<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

use OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class LeadControllersTest extends AbstractDatagridTestCase
{
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

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('orocrm_sales_lead_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('orocrm_sales_lead_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['orocrm_sales_lead_form[name]']                = $name;
        $form['orocrm_sales_lead_form[firstName]']           = 'firstName';
        $form['orocrm_sales_lead_form[lastName]']            = 'lastName';
        $form['orocrm_sales_lead_form[address][city]']       = 'City Name';
        $form['orocrm_sales_lead_form[address][label]']      = 'Main Address';
        $form['orocrm_sales_lead_form[address][postalCode]'] = '10000';
        $form['orocrm_sales_lead_form[address][street2]']    = 'Second Street';
        $form['orocrm_sales_lead_form[address][street]']     = 'Main Street';
        $form['orocrm_sales_lead_form[companyName]']         = 'Company';
        $form['orocrm_sales_lead_form[emails][0][email]']    = 'test@example.test';
        $form['orocrm_sales_lead_form[owner]']               = 1;
        $form['orocrm_sales_lead_form[dataChannel]']         = $this->getReference('default_channel')->getId();

        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<select name="orocrm_sales_lead_form[address][country]" id="orocrm_sales_lead_form_address_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="US">United States</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $doc->loadHTML(
            '<select name="orocrm_sales_lead_form[address][region]" id="orocrm_sales_lead_form_address_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="US-CA">California</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);

        $form['orocrm_sales_lead_form[address][country]'] = 'US';
        $form['orocrm_sales_lead_form[address][region]'] = 'US-CA';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Lead saved", $crawler->html());

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
            'sales-lead-grid',
            ['sales-lead-grid[_filter][name][value]' => $name]
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $returnValue = $result;
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_lead_update', ['id' => $result['id']])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . $this->generateRandomString();
        $form['orocrm_sales_lead_form[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Lead saved", $crawler->html());

        $returnValue['name'] = $name;

        return $returnValue;
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testView($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_lead_view', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("{$returnValue['name']} - Leads - Sales", $crawler->html());
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testInfo($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_sales_lead_info',
                ['id' => $returnValue['id'], '_widgetContainer' => 'block']
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains($returnValue['firstName'], $crawler->html());
        $this->assertContains($returnValue['lastName'], $crawler->html());
    }

    /**
     * @param array $returnValue
     * @depends testUpdate
     */
    public function testDelete($returnValue)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_lead', ['id' => $returnValue['id']])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_sales_lead_view', ['id' => $returnValue['id']])
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
            'Lead grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-lead-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name'        => 'Lead name',
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'firstName'   => 'fname',
                        'lastName'    => 'lname',
                        'email'       => 'email@email.com'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Lead grid with filters'   => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-lead-grid'
                    ],
                    'gridFilters'         => [
                        'sales-lead-grid[_filter][name][value]' => 'Lead name',
                    ],
                    'assert'              => [
                        'name'        => 'Lead name',
                        'channelName' => LoadSalesBundleFixtures::CHANNEL_NAME,
                        'firstName'   => 'fname',
                        'lastName'    => 'lname',
                        'email'       => 'email@email.com'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Lead grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'sales-lead-grid'
                    ],
                    'gridFilters'         => [
                        'sales-lead-grid[_filter][name][value]' => 'some name',
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
