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
class OpportunityControllersTest extends WebTestCase
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
        $this->client->request('GET', $this->client->generate('orocrm_sales_opportunity_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('orocrm_sales_opportunity_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . ToolsAPI::generateRandomString();
        $form['orocrm_sales_opportunity_form[name]']               = $name;
        $form['orocrm_sales_opportunity_form[probability]']         = 50;
        $form['orocrm_sales_opportunity_form[budgetAmount]']         = 10000;
        $form['orocrm_sales_opportunity_form[customerNeed]']         = 10001;
        $form['orocrm_sales_opportunity_form[closeReason]']         = 'cancelled';
        $form['orocrm_sales_opportunity_form[owner]']         = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Opportunity saved", $crawler->html());

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
            $this->client->generate('orocrm_sales_opportunity_index', array('_format' =>'json')),
            array(
                'opportunity[_filter][name][type]=3' => '3',
                'opportunity[_filter][name][value]' => $name,
                'opportunity[_pager][_page]' => '1',
                'opportunity[_pager][_per_page]' => '10',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_opportunity_update', array('id' => $result['id']))
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $name = 'name' . ToolsAPI::generateRandomString();
        $form['orocrm_sales_opportunity_form[name]'] = $name;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Opportunity saved", $crawler->html());

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
            $this->client->generate('orocrm_sales_opportunity_index', array('_format' =>'json')),
            array(
                'opportunity[_filter][name][type]=3' => '3',
                'opportunity[_filter][name][value]' => $name,
                'opportunity[_pager][_page]' => '1',
                'opportunity[_pager][_per_page]' => '10',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_opportunity_view', array('id' => $result['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("{$name} - Opportunities - Sales", $crawler->html());
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
            $this->client->generate('orocrm_sales_opportunity_index', array('_format' =>'json')),
            array(
                'opportunity[_filter][name][type]=3' => '3',
                'opportunity[_filter][name][value]' => $name,
                'opportunity[_pager][_page]' => '1',
                'opportunity[_pager][_per_page]' => '10',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $expectedResult = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate(
                'orocrm_sales_opportunity_info',
                array('id' => $expectedResult['id'], '_widgetContainer' => 'block')
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    /**
     * @param $name
     * @depends testUpdate
     */
    public function testDelete($name)
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_sales_opportunity_index', array('_format' =>'json')),
            array(
                'opportunity[_filter][name][type]=3' => '3',
                'opportunity[_filter][name][value]' => $name,
                'opportunity[_pager][_page]' => '1',
                'opportunity[_pager][_per_page]' => '10',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_opportunity', array('id' => $result['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
    }
}
