<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;

class ControllersGroupTest extends WebTestCase
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
        $this->client->request('GET', $this->getUrl('oro_contact_group_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->getUrl('oro_contact_group_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_contact_group_form[label]'] = 'Contact Group Label';
        $form['oro_contact_group_form[owner]'] = 1;
        //$form['oro_contact_group_form[appendContacts]'] = 1;
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Group saved", $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $response = $this->client->requestGrid(
            'contact-groups-grid',
            array('contact-groups-grid[_filter][label][value]' => 'Contact Group Label')
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $id = $result['id'];
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_contact_group_update', array('id' => $result['id']))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_contact_group_form[label]'] = 'Contact Group Label Updated';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Group saved", $crawler->html());

        return $id;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete($id)
    {
        $this->ajaxRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_contactgroup', array('id' => $id))
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
    }
}
