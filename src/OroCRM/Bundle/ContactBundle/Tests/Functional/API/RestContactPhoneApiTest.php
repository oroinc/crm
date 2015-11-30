<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestContactPhoneApiTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([
            'OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData'
        ]);
    }

    public function testCreateContactPhone()
    {
        $contact = $this->getReference('Contact_Brenda');
        $content = json_encode([
            'contactId' => $contact->getId(),
            'phone' => '111',
            'primary' => true
        ]);
        $this->client->request('POST', $this->getUrl('oro_api_post_contact_phone'), [], [], [], $content);
        $contact = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $contact);
        $this->assertNotEmpty($contact['id']);
    }

    public function testCreateSecondPrimaryPhone()
    {
        $contact = $this->getReference('Contact_Brenda');

        $content = json_encode([
            'contactId' => $contact->getId(),
            'phone' =>'test1@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), 400);
    }

    public function testEmptyContactId()
    {
        $content = json_encode([
            'phone' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), 400);
    }

    public function testEmptyPhone()
    {
        $contact = $this->getReference('Contact_Brenda');
        $content = json_encode([
            'contactId' => $contact->getId(),
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), 400);
    }
}
