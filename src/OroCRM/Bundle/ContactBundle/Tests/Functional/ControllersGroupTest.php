<?php

namespace OroCrRM\Bundle\ContactBundle\Tests\Functional;

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
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_group_index', array('_format' =>'json')),
            array(
                'contact_groups[_filter][label][value]' => 'Contact Group Label',
                'contact_groups[_filter][label][type]' => '1',
                'contacts[_pager][_per_page]' => '10',
                'contacts[_sort_by][first_name]' => 'ASC',
                'contacts[_sort_by][last_name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

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
    }

    /**
     * @depends testCreate
     */
    public function testGrid()
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_group_index', array('_format' =>'json')),
            array(
                'contact_groups[_filter][label][value]' => 'Contact Group Label',
                'contact_groups[_filter][label][type]' => '1',
                'contacts[_pager][_per_page]' => '10',
                'contacts[_sort_by][first_name]' => 'ASC',
                'contacts[_sort_by][last_name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_group_contact_grid', array('id' => $result['id']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result);
        $this->assertEquals(0, $result['options']['TotalRecords']);
    }

    /**
     * @depends testUpdate
     */
    public function testDelete()
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_group_index', array('_format' =>'json')),
            array(
                'contact_groups[_filter][label][value]' => 'Contact Group Label Updated',
                'contact_groups[_filter][label][type]' => '1',
                'contacts[_pager][_per_page]' => '10',
                'contacts[_sort_by][first_name]' => 'ASC',
                'contacts[_sort_by][last_name]' => 'ASC',
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_contactgroup', array('id' => $result['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
    }
}
