<?php

namespace OroCRM\Bundle\CallBundle\Tests\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class CallControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateBasicHeader());
        $this->client = static::createClient(array(), array_merge(ToolsAPI::generateBasicHeader(), array('HTTP_X-CSRF-Header' => 1)));
    }
    
    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('orocrm_call_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testView()
    {
        $this->client->request('GET', $this->client->generate('orocrm_call_view', array('id' => 1)));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }
    

    public function testCreateSubject()
    {
        $crawler = $this->client->request('GET', $this->client->generate('orocrm_call_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("This value should not be blank", $crawler->html());
    }

    public function testCreatePhoneNumber()
    {
        $crawler = $this->client->request('GET', $this->client->generate('orocrm_call_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_call_call_form[subject]'] = 'Subject';
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Phone number is required field", $crawler->html());
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('orocrm_call_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_call_call_form[subject]'] = 'Subject';
        $form['orocrm_call_call_form[callDateTime]'] = '0000-00-00 00:00:00';
        $form['orocrm_call_call_form[callStatus]'] = '2';
        $form['orocrm_call_call_form[direction]'] = '2';
        $form['orocrm_call_call_form[owner]'] = '1';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Loading...", $crawler->html());
    }
}
