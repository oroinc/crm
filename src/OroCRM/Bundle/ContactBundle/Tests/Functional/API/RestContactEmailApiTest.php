<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestContactEmailApiTest extends WebTestCase
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
            'email' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $contact = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $contact);
        $this->assertNotEmpty($contact['id']);
    }

    public function testCreateSecondPrimaryContactEmail()
    {
        $contact = $this->getReference('Contact_Brenda');
        $content = json_encode([
            'contactId' => $contact->getId(),
            'email' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), 201);

        $content = json_encode([
            'contactId' => $contact->getId(),
            'email' =>'test1@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), 400);
    }

    public function testEmptyContactId()
    {
        $content = json_encode([
            'email' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), 400);
    }

    public function testEmptyEmail()
    {
        $contact = $this->getReference('Contact_Brenda');
        $content = json_encode([
            'contactId' => $contact->getId(),
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), 400);
    }
}
