<?php

namespace OroCrRM\Bundle\SalesBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class LeadControllersTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(
            array(),
            array_merge(ToolsAPI::generateBasicHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('orocrm_sales_lead_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('orocrm_sales_lead_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . ToolsAPI::generateRandomString();
        $form['orocrm_sales_lead_form[name]']                = $name;
        $form['orocrm_sales_lead_form[firstName]']           = 'firstName';
        $form['orocrm_sales_lead_form[lastName]']            = 'lastName';
        $form['orocrm_sales_lead_form[address][city]']       = 'City Name';
        $form['orocrm_sales_lead_form[address][label]']      = 'Main Address';
        $form['orocrm_sales_lead_form[address][postalCode]'] = '10000';
        $form['orocrm_sales_lead_form[address][street2]']    = 'Second Street';
        $form['orocrm_sales_lead_form[address][street]']     = 'Main Street';
        $form['orocrm_sales_lead_form[companyName]']         = 'Company';
        $form['orocrm_sales_lead_form[email]']               = 'test@example.test';
        $form['orocrm_sales_lead_form[owner]']               = 1;

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
            '<select name="orocrm_sales_lead_form[address][state]" id="orocrm_sales_lead_form_address_state" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="US.CA">California</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);

        $form['orocrm_sales_lead_form[address][country]'] = 'US';
        $form['orocrm_sales_lead_form[address][state]'] = 'US.CA';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Lead saved", $crawler->html());

        return $name;
    }

    /**
     * @param $name
     * @depends testCreate
     *
     * @return string
     */
    public function testUpdate($name)
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_lead_index', array('_format' =>'json')),
            array(
                'leads[_filter][name][type]=3' => '3',
                'leads[_filter][name][value]' => $name,
                'leads[_pager][_page]' => '1',
                'leads[_pager][_per_page]' => '10',
                'leads[_sort_by][first_name]' => 'ASC',
                'leads[_sort_by][last_name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_lead_update', array('id' => $result['id']))
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . ToolsAPI::generateRandomString();
        $form['orocrm_sales_lead_form[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Lead saved", $crawler->html());

        return $name;
    }

    /**
     * @param $name
     * @depends testUpdate
     *
     * @return string
     */
    public function testView($name)
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_lead_index', array('_format' =>'json')),
            array(
                'leads[_filter][name][type]=3' => '3',
                'leads[_filter][name][value]' => $name,
                'leads[_pager][_page]' => '1',
                'leads[_pager][_per_page]' => '10',
                'leads[_sort_by][first_name]' => 'ASC',
                'leads[_sort_by][last_name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_lead_view', array('id' => $result['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("{$name} - Leads - Sales", $crawler->html());
    }

    /**
     * @param $name
     * @depends testUpdate
     *
     * @return string
     */
    public function testInfo($name)
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_lead_index', array('_format' =>'json')),
            array(
                'leads[_filter][name][type]=3' => '3',
                'leads[_filter][name][value]' => $name,
                'leads[_pager][_page]' => '1',
                'leads[_pager][_per_page]' => '10',
                'leads[_sort_by][first_name]' => 'ASC',
                'leads[_sort_by][last_name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $expectedResult = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate(
                'orocrm_sales_lead_info',
                array('id' => $expectedResult['id'], '_widgetContainer' => 'block')
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains($expectedResult['first_name'], $crawler->html());
        $this->assertContains($expectedResult['last_name'], $crawler->html());
    }

    /**
     * @param $name
     * @depends testUpdate
     */
    public function testDelete($name)
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_lead_index', array('_format' =>'json')),
            array(
                'leads[_filter][name][type]=3' => '3',
                'leads[_filter][name][value]' => $name,
                'leads[_pager][_page]' => '1',
                'leads[_pager][_per_page]' => '10',
                'leads[_sort_by][first_name]' => 'ASC',
                'leads[_sort_by][last_name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_lead', array('id' => $result['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
    }
}
