<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;

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


    /**
     * @depend testCreate
     * @return int
     */
    public function testUpdate()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'contacts-grid',
            array(
                'contacts-grid[_filter][firstName][value]' => 'Contact_fname',
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

    /**
     * @depends testCreate
     */
    public function testMassAction()
    {
        for ($i = 1; $i <=5; $i++) {
            $crawler = $this->client->request('GET', $this->client->generate('orocrm_contact_create'));
            /** @var Form $form */
            $form = $crawler->selectButton('Save and Close')->form();
            $form['orocrm_contact_form[firstName]'] = 'Contact_fname' . ToolsAPI::randomGen(5);
            $form['orocrm_contact_form[lastName]'] = 'Contact_lname' . ToolsAPI::randomGen(5);
            $form['orocrm_contact_form[owner]'] = '1';

            $this->client->followRedirects(true);
            $crawler = $this->client->submit($form);

            $result = $this->client->getResponse();
            ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
            $this->assertContains("Contact saved", $crawler->html());
        }

        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'contacts-grid',
            array()
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $id = array();
        foreach ($result['data'] as $value) {
            $id[] = $value['id'];
        }
        $id = implode(',', $id);
        $this->client->request(
            'GET',
            $this->client->generate(
                'oro_datagrid_mass_action',
                array('gridName' => 'contacts-grid', 'actionName' => 'delete', 'values' => $id, 'inset' => 1)
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());

        $this->assertTrue($result['successful']);
        $this->assertEquals("5 entities were removed", $result['message']);
        $this->assertEquals(5, $result['count']);

        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'contacts-grid',
            array()
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEmpty($result['data']);
    }
}
