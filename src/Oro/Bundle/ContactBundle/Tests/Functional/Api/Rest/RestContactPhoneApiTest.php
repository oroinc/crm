<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\Rest;

use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactPhoneData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestContactPhoneApiTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadContactPhoneData::class]);
    }

    public function testCreateContactPhone()
    {
        $contact = $this->getReference('Contact_'. LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $payload = [
            'contactId' => $contact->getId(),
            'phone' => '111',
            'primary' => true
        ];
        $this->client->jsonRequest('POST', $this->getUrl('oro_api_post_contact_phone'), $payload);
        $contact = $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_CREATED);

        $this->assertArrayHasKey('id', $contact);
        $this->assertNotEmpty($contact['id']);
    }

    public function testCreateSecondPrimaryPhone()
    {
        $contact = $this->getReference('Contact_'. LoadContactEntitiesData::THIRD_ENTITY_NAME);

        $payload = [
            'contactId' => $contact->getId(),
            'phone' =>'test1@test.test',
            'primary' => true
        ];

        $this->client->jsonRequest('POST', $this->getUrl('oro_api_post_contact_phone'), $payload);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyContactId()
    {
        $payload = [
            'phone' =>'test@test.test',
            'primary' => true
        ];

        $this->client->jsonRequest('POST', $this->getUrl('oro_api_post_contact_phone'), $payload);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyPhone()
    {
        $contact = $this->getReference('Contact_'. LoadContactEntitiesData::THIRD_ENTITY_NAME);
        $payload = [
            'contactId' => $contact->getId(),
            'primary' => true
        ];

        $this->client->jsonRequest('POST', $this->getUrl('oro_api_post_contact_phone'), $payload);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testDeletePhoneForbidden()
    {
        $contactPhone = $this->getReference('ContactPhone_Several_'. LoadContactPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $contactPhone->getId()
        ];
        $this->client->jsonRequest('DELETE', $this->getUrl('oro_api_delete_contact_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        $realResponse = json_decode($this->client->getResponse()->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(403, $realResponse->code);
        $this->assertEquals(
            'The delete operation is forbidden. Reason: '
            . 'Phone number was not deleted, the contact has '
            . 'more than one phone number, can\'t set the new primary.',
            $realResponse->message
        );
        $this->assertNotEmpty($realResponse->errors->errors);
    }

    public function testCanDeleteNonPrimaryPhone()
    {
        $contactPhone = $this->getReference('ContactPhone_Several_'. LoadContactPhoneData::THIRD_ENTITY_NAME);
        $routeParams = [
            'id' => $contactPhone->getId()
        ];
        $this->client->jsonRequest('DELETE', $this->getUrl('oro_api_delete_contact_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }

    public function testDeleteContactInformationForbidden()
    {
        $contact = $this->getReference('Contact_' . LoadContactEntitiesData::FOURTH_ENTITY_NAME);
        $this->client->jsonRequest(
            'PATCH',
            $this->getUrl('oro_api_patch_entity_data', [
                'className' => 'Oro_Bundle_ContactBundle_Entity_Contact',
                'id' => $contact->getId()
            ]),
            ['firstName' => '', 'lastName' => '']
        );

        $contactEmail = $this->getReference('ContactPhone_Single_'. LoadContactPhoneData::FOURTH_ENTITY_NAME);
        $routeParams = [
            'id' => $contactEmail->getId()
        ];
        $this->client->jsonRequest('DELETE', $this->getUrl('oro_api_delete_contact_phone', $routeParams));
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        $realResponse = json_decode($this->client->getResponse()->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(403, $realResponse->code);
        $this->assertEquals(
            'The delete operation is forbidden. Reason: '
            . 'At least one of the fields First name, Last name, Emails or Phones must be defined.',
            $realResponse->message
        );
        $this->assertNotEmpty($realResponse->errors->errors);
    }

    public function testDeletePhoneSuccess()
    {
        $contactPhone = $this->getReference('ContactPhone_Single_'. LoadContactPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $contactPhone->getId()
        ];
        $this->client->jsonRequest('DELETE', $this->getUrl('oro_api_delete_contact_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
