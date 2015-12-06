<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use FOS\RestBundle\Util\Codes;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEmailData;
use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;

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
            'OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEmailData'
        ]);
    }

    public function testCreateContactEmail()
    {
        $contact = $this->getReference('Contact_' . LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $content = json_encode([
            'contactId' => $contact->getId(),
            'email' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $contact = $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_CREATED);

        $this->assertArrayHasKey('id', $contact);
        $this->assertNotEmpty($contact['id']);
    }

    public function testCreateSecondPrimaryEmail()
    {
        $contact = $this->getReference('Contact_Brenda');
        $content = json_encode([
            'contactId' => $contact->getId(),
            'email' =>'test1@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyContactId()
    {
        $content = json_encode([
            'email' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyEmail()
    {
        $contact = $this->getReference('Contact_Brenda');
        $content = json_encode([
            'contactId' => $contact->getId(),
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testDeleteEmailForbidden()
    {
        $contactEmail = $this->getReference('ContactEmail_Several_'. LoadContactEmailData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $contactEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_contact_email', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(
            '{"code":500,"message":"oro.contact.email.error.delete.more_one"}',
            $this->client->getResponse()->getContent()
        );
    }

    public function testDeleteEmailSuccess()
    {
        $contactEmail = $this->getReference('ContactEmail_Single_'. LoadContactEmailData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $contactEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_contact_email', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
