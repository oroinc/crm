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
        $this->client->request('GET', $this->client->generate('orocrm_contact_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('orocrm_contact_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_contact_form[firstName]'] = 'Contact_fname';
        $form['orocrm_contact_form[lastName]'] = 'Contact_lname';
        $form['orocrm_contact_form[owner]'] = '1';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Contact saved", $crawler->html());
    }

    public function testUpdate()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'contacts-grid',
            array(
                'contacts-grids[_filter][name][value]' => 'Contact_fname',
                'contacts-grids[_pager][_page]' => '1',
                'contacts-grids[_pager][_per_page]' => '10',
                'contacts-grids[_sort_by][name]' => 'ASC',
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);
        $id = $result['id'];
        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_update', array('id' => $id))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['orocrm_contact_form[firstName]'] = 'Contact_fname_updated';
        $form['orocrm_contact_form[lastName]'] = 'Contact_lname_updated';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Contact saved", $crawler->html());

        return $id;
    }

    /**
     * @depends testUpdate
     * @param $id
     */
    public function testView($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertRegExp("/Contact_fname_updated\s+Contact_lname_updated - Contacts - Customers/", $crawler->html());
    }

    /**
     * @depends testUpdate
     * @param $id
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_contact', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404, 'text/html; charset=UTF-8');
    }
}
