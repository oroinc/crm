<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use FOS\RestBundle\Util\Codes;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactPhoneData;

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
            'OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactPhoneData'
        ]);
    }

    public function testCreateContactPhone()
    {
        $contact = $this->getReference('Contact_'. LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $content = json_encode([
            'contactId' => $contact->getId(),
            'phone' => '111',
            'primary' => true
        ]);
        $this->client->request('POST', $this->getUrl('oro_api_post_contact_phone'), [], [], [], $content);
        $contact = $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_CREATED);

        $this->assertArrayHasKey('id', $contact);
        $this->assertNotEmpty($contact['id']);
    }

    public function testCreateSecondPrimaryPhone()
    {
        $contact = $this->getReference('Contact_'. LoadContactEntitiesData::THIRD_ENTITY_NAME);

        $content = json_encode([
            'contactId' => $contact->getId(),
            'phone' =>'test1@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyContactId()
    {
        $content = json_encode([
            'phone' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyPhone()
    {
        $contact = $this->getReference('Contact_'. LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $content = json_encode([
            'contactId' => $contact->getId(),
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testDeletePhoneForbidden()
    {
        $contactPhone = $this->getReference('ContactPhone_Several_'. LoadContactPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $contactPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_contact_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(
            '{"code":500,"message":"oro.contact.phone.error.delete.more_one"}',
            $this->client->getResponse()->getContent()
        );
    }

    public function testDeletePhoneSuccess()
    {
        $contactPhone = $this->getReference('ContactPhone_Single_'. LoadContactPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $contactPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_contact_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
