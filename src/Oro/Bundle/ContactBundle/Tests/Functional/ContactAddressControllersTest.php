<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;

class ContactAddressControllersTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient(
            array(),
            $this->generateBasicAuthHeader()
        );
        $this->client->useHashNavigation(true);
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_contact_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_contact_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_contact_form[firstName]'] = 'Contact_fname';
        $form['oro_contact_form[lastName]'] = 'Contact_lname';
        $form['oro_contact_form[owner]'] = '1';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Contact saved", $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testCreateAddress()
    {
        $response = $this->client->requestGrid(
            'contacts-grid',
            array('contacts-grid[_filter][firstName][value]' => 'Contact_fname')
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        $id = $result['id'];
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_contact_address_create',
                array('contactId' => $result['id'], '_widgetContainer' => 'dialog')
            )
        );

        $result = $this->client->getResponse();
        $this->assertEquals(200, $result->getStatusCode());

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();
        $formNode = $form->getNode();
        $formNode->setAttribute('action', $formNode->getAttribute('action') . '?_widgetContainer=dialog');

        $form['oro_contact_address_form[street]'] = 'Street';
        $form['oro_contact_address_form[city]'] = 'City';
        $form['oro_contact_address_form[postalCode]'] = 'Zip code';

        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<select name="oro_contact_address_form[country]" id="oro_contact_address_form_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF">Afghanistan</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_contact_address_form[country]'] = 'AF';

        $doc->loadHTML(
            '<select name="oro_contact_address_form[region]" id="oro_contact_address_form_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF-BDS">Badakhshān</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_contact_address_form[region]'] = 'AF-BDS';

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->ajaxRequest(
            'GET',
            $this->getUrl('oro_api_get_contact_address_primary', array('contactId' => $id))
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals('Badakhshān', $result['region']);

        return $id;
    }

    /**
     * @depends testCreateAddress
     */
    public function testUpdateAddress($id)
    {
        $this->ajaxRequest(
            'GET',
            $this->getUrl('oro_api_get_contact_address_primary', array('contactId' => $id))
        );

        $address = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_contact_address_update',
                array('contactId' => $id, 'id' => $address['id'], '_widgetContainer' => 'dialog')
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
            '<select name="oro_contact_address_form[country]" id="oro_contact_address_form_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW">Zimbabwe</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_contact_address_form[country]'] = 'ZW';

        $doc->loadHTML(
            '<select name="oro_contact_address_form[region]" id="oro_contact_address_form_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW-MA">Manicaland</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_contact_address_form[region]'] = 'ZW-MA';

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->ajaxRequest(
            'GET',
            $this->getUrl('oro_api_get_contact_address_primary', array('contactId' => $id))
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals('Manicaland', $result['region']);
    }
}
