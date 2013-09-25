<?php

namespace OroCrRM\Bundle\AccountBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class ControllersTest extends WebTestCase
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
        $this->client->request('GET', $this->client->generate('orocrm_account_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('orocrm_account_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_account_form[name]'] = 'Account_name';
        $form['orocrm_account_form[owner]'] = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Account saved", $crawler->html());
    }

    public function testUpdate()
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_account_index', array('_format' =>'json')),
            array(
                'accounts[_filter][name][type]=1' => '1',
                'accounts[_filter][name][value]' => 'Account_name',
                'accounts[_pager][_page]' => '1',
                'accounts[_pager][_per_page]' => '10',
                'accounts[_sort_by][name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_account_update', array('id' => $result['id']))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_account_form[name]'] = 'Account_name';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Account saved", $crawler->html());
    }

    public function testView()
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_account_index', array('_format' =>'json')),
            array(
                'accounts[_filter][name][type]=1' => '1',
                'accounts[_filter][name][value]' => 'Account_name',
                'accounts[_pager][_page]' => '1',
                'accounts[_pager][_per_page]' => '10',
                'accounts[_sort_by][name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_account_view', array('id' => $result['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Account_name - Accounts - Customers", $crawler->html());
    }

    public function testDelete()
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_account_index', array('_format' =>'json')),
            array(
                'accounts[_filter][name][type]=1' => '1',
                'accounts[_filter][name][value]' => 'Account_name',
                'accounts[_pager][_page]' => '1',
                'accounts[_pager][_per_page]' => '10',
                'accounts[_sort_by][name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_account', array('id' => $result['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
    }
}
