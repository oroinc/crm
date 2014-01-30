<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional;

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
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'sales-lead-grid',
            array(
                'sales-lead-grid[_filter][name][value]' => $name,
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);
        $returnValue = $result;
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

        $returnValue['name'] = $name;
        return $returnValue;
    }

    /**
     * @param $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testView($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_lead_view', array('id' => $returnValue['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("{$returnValue['name']} - Leads - Sales", $crawler->html());
    }

    /**
     * @param $returnValue
     * @depends testUpdate
     *
     * @return string
     */
    public function testInfo($returnValue)
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->generate(
                'orocrm_sales_lead_info',
                array('id' => $returnValue['id'], '_widgetContainer' => 'block')
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains($returnValue['firstName'], $crawler->html());
        $this->assertContains($returnValue['lastName'], $crawler->html());
    }

    /**
     * @param $returnValue
     * @depends testUpdate
     */
    public function testDelete($returnValue)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_lead', array('id' => $returnValue['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_lead_view', array('id' => $returnValue['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404, 'text/html; charset=UTF-8');
    }
}
