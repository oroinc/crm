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
class ContactAddressControllersTest extends WebTestCase
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
     * @depends testCreate
     */
    public function testCreateAddress()
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_index', array('_format' => 'json')),
            array(
                'contacts[_filter][first_name][value]' => 'Contact_fname',
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
            $this->client->generate(
                'orocrm_contact_address_create',
                array('contactId' => $result['id'], '_widgetContainer' => 'dialog')
            )
        );

        $result = $this->client->getResponse();
        $this->assertEquals(200, $result->getStatusCode());

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();
        $formNode = $form->getNode();
        $formNode->setAttribute('action', $formNode->getAttribute('action') . '?_widgetContainer=dialog');

        $form['orocrm_contact_address_form[street]'] = 'Street';
        $form['orocrm_contact_address_form[city]'] = 'City';
        $form['orocrm_contact_address_form[postalCode]'] = 'Zip code';

        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<select name="orocrm_contact_address_form[country]" id="orocrm_contact_address_form_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF">Afghanistan</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['orocrm_contact_address_form[country]'] = 'AF';

        $doc->loadHTML(
            '<select name="orocrm_contact_address_form[state]" id="orocrm_contact_address_form_state" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF.01">Badakhshan</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['orocrm_contact_address_form[state]'] = 'AF.01';

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');

        $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_index', array('_format' => 'json')),
            array(
                'contacts[_filter][first_name][value]' => 'Contact_fname',
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
            $this->client->generate('oro_api_get_contact_address_primary', array('contactId' => $result['id']))
        );

        $result = $this->client->getResponse();
        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals('Badakhshan', $result['state']);
    }

    /**
     * @depends testCreateAddress
     */
    public function testUpdateAddress()
    {
        $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_index', array('_format' => 'json')),
            array(
                'contacts[_filter][first_name][value]' => 'Contact_fname',
                'contacts[_pager][_per_page]' => '10',
                'contacts[_sort_by][first_name]' => 'ASC',
                'contacts[_sort_by][last_name]' => 'ASC',
            )
        );

        $contact = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($contact, 200);
        $contact = ToolsAPI::jsonToArray($contact->getContent());
        $contact = reset($contact['data']);

        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_contact_address_primary', array('contactId' => $contact['id']))
        );

        $address = $this->client->getResponse();
        $address = ToolsAPI::jsonToArray($address->getContent());

        $crawler = $this->client->request(
            'GET',
            $this->client->generate(
                'orocrm_contact_address_update',
                array('contactId' => $contact['id'], 'id' => $address['id'], '_widgetContainer' => 'dialog')
            )
        );

        $result = $this->client->getResponse();
        $this->assertEquals(200, $result->getStatusCode());

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();
        $formNode = $form->getNode();
        $formNode->setAttribute('action', $formNode->getAttribute('action') . '?_widgetContainer=dialog');

        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<select name="orocrm_contact_address_form[country]" id="orocrm_contact_address_form_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW">Zimbabwe</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['orocrm_contact_address_form[country]'] = 'ZW';

        $doc->loadHTML(
            '<select name="orocrm_contact_address_form[state]" id="orocrm_contact_address_form_state" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW.01">Manicaland</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['orocrm_contact_address_form[state]'] = 'ZW.01';

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');

        $this->client->request(
            'GET',
            $this->client->generate('orocrm_contact_index', array('_format' => 'json')),
            array(
                'contacts[_filter][first_name][value]' => 'Contact_fname',
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
            $this->client->generate('oro_api_get_contact_address_primary', array('contactId' => $result['id']))
        );

        $result = $this->client->getResponse();
        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->assertEquals('Manicaland', $result['state']);
    }
}
