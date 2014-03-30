<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Functional;

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

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'accounts-grid',
            array(
                'accounts-grid[_filter][name][value]' => 'Account_name',
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);
        $id = $result['id'];
        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_account_update', array('id' => $result['id']))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_account_form[name]'] = 'Account_name_update';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Account saved", $crawler->html());

        return $id;
    }

    /**
     * @depends testUpdate
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_account_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Account_name_update - Accounts - Customers", $crawler->html());
    }

    /**
     * @depends testUpdate
     */
    public function testContactWidget($id)
    {
        $this->client->request(
            'GET',
            $this->client->generate(
                'orocrm_account_widget_contacts_info',
                array('id' => $id, '_widgetContainer' => 'dialog')
            )
        );
        //just verify method OK
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    /**
     * @depends testUpdate
     */
    public function testContactUpdateGrid($id)
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'account-contacts-update-grid',
            array(
                'account-contacts-update-grid[account]' => $id
            )
        );
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEmpty($result['data']);
        $this->assertEquals(0, $result['options']['totalRecords']);
    }

    /**
     * @depends testUpdate
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_account', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('orocrm_account_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404, 'text/html; charset=UTF-8');
    }
}
