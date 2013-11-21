<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class ControllersGroupTest extends WebTestCase
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
        $this->client->request('GET', $this->client->generate('orocrm_contact_group_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('orocrm_contact_group_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_contact_group_form[label]'] = 'Contact Group Label';
        $form['orocrm_contact_group_form[owner]'] = 1;
        //$form['orocrm_contact_group_form[appendContacts]'] = 1;
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, '');
        $this->assertContains("Group saved", $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'contact-groups-grid',
            array(
                'contact-groups-grid[_filter][label][value]' => 'Contact Group Label',
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);
        $id = $result['id'];
        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_group_update', array('id' => $result['id']))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_contact_group_form[label]'] = 'Contact Group Label Updated';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, '');
        $this->assertContains("Group saved", $crawler->html());

        return $id;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_contactgroup', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
    }
}
