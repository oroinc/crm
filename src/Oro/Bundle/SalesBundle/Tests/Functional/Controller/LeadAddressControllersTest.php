<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller;

use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

class LeadAddressControllersTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient(
            array(),
            $this->generateBasicAuthHeader()
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(['Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);
    }

    public function testCreateAddress()
    {
        $id = $this->getReference('default_lead')->getId();
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_sales_lead_address_create',
                array('leadId' => $id, '_widgetContainer' => 'dialog')
            )
        );

        $result = $this->client->getResponse();
        $this->assertEquals(200, $result->getStatusCode());

        $form = $crawler->selectButton('Save')->form();
        $formNode = $form->getNode();
        $formNode->setAttribute('action', $formNode->getAttribute('action') . '?_widgetContainer=dialog');

        $form['oro_sales_lead_address_form[street]'] = 'Street';
        $form['oro_sales_lead_address_form[city]'] = 'City';
        $form['oro_sales_lead_address_form[postalCode]'] = 'Zip code';

        $doc = new \DOMDocument('1.0');
        $doc->loadHTML(
            '<select name="oro_sales_lead_address_form[country]" id="oro_sales_lead_address_form_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF">Afghanistan</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_sales_lead_address_form[country]'] = 'AF';

        $doc->loadHTML(
            '<select name="oro_sales_lead_address_form[region]" id="oro_sales_lead_address_form_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="AF-BDS">Badakhshān</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_sales_lead_address_form[region]'] = 'AF-BDS';

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->ajaxRequest(
            'GET',
            $this->getUrl('oro_api_get_lead_address_primary', ['leadId' => $id])
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
            $this->getUrl('oro_api_get_lead_address_primary', ['leadId' => $id])
        );

        $address = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_sales_lead_address_update',
                array('leadId' => $id, 'id' => $address['id'], '_widgetContainer' => 'dialog')
            )
        );

        $result = $this->client->getResponse();
        $this->assertEquals(200, $result->getStatusCode());

        $form = $crawler->selectButton('Save')->form();
        $formNode = $form->getNode();
        $formNode->setAttribute('action', $formNode->getAttribute('action') . '?_widgetContainer=dialog');

        $doc = new \DOMDocument('1.0');
        $doc->loadHTML(
            '<select name="oro_sales_lead_address_form[country]" id="oro_sales_lead_address_form_country" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW">Zimbabwe</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_sales_lead_address_form[country]'] = 'ZW';

        $doc->loadHTML(
            '<select name="oro_sales_lead_address_form[region]" id="oro_sales_lead_address_form_region" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="ZW-MA">Manicaland</option> </select>'
        );
        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['oro_sales_lead_address_form[region]'] = 'ZW-MA';

        $this->client->followRedirects(true);
        $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $this->ajaxRequest(
            'GET',
            $this->getUrl('oro_api_get_lead_address_primary', ['leadId' => $id])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals('Manicaland', $result['region']);

        return $id;
    }

    /**
     * @depends testUpdateAddress
     */
    public function testDeleteAddress($id)
    {
        $this->ajaxRequest(
            'GET',
            $this->getUrl('oro_api_get_lead_address_primary', ['leadId' => $id])
        );
        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $addressId = $result['id'];

        $this->ajaxRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_lead_address', ['leadId' => $id, 'addressId' => $addressId])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $registry = $this->getContainer()->get('doctrine');
        $em = $registry->getManagerForClass(Lead::class);
        $lead = $em->find(Lead::class, $id);
        $this->assertNotNull($lead);
        $this->assertNull($lead->getPrimaryAddress());
    }
}
