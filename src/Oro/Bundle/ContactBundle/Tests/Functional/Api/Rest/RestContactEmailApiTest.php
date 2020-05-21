<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\Rest;

use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEmailData;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestContactEmailApiTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadContactEmailData::class]);
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
        $contact = $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_CREATED);

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
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyContactId()
    {
        $content = json_encode([
            'email' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyEmail()
    {
        $contact = $this->getReference('Contact_Brenda');
        $content = json_encode([
            'contactId' => $contact->getId(),
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_contact_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testDeleteEmailForbidden()
    {
        $contactEmail = $this->getReference('ContactEmail_Several_'. LoadContactEmailData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $contactEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_contact_email', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);

        $realResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(403, $realResponse->code);
        $this->assertEquals(
            'The delete operation is forbidden. Reason: '
            . 'Email address was not deleted, the contact '
            . 'has more than one email addresses, can\'t set the new primary.',
            $realResponse->message
        );
    }

    public function testCanDeleteNonPrimaryEmail()
    {
        $contactEmail = $this->getReference('ContactEmail_Several_'. LoadContactEmailData::THIRD_ENTITY_NAME);
        $routeParams = [
            'id' => $contactEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_contact_email', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }

    public function testDeleteContactInformationForbidden()
    {
        $contact = $this->getReference('Contact_' . LoadContactEntitiesData::FOURTH_ENTITY_NAME);
        $this->client->request(
            'PATCH',
            $this->getUrl('oro_api_patch_entity_data', [
                'className' => 'Oro_Bundle_ContactBundle_Entity_Contact',
                'id' => $contact->getId()
            ]),
            [],
            [],
            [],
            '{"firstName": "", "lastName": ""}'
        );

        $contactEmail = $this->getReference('ContactEmail_Single_'. LoadContactEmailData::FOURTH_ENTITY_NAME);
        $routeParams = [
            'id' => $contactEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_contact_email', $routeParams));
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        $realResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(403, $realResponse->code);
        $this->assertEquals(
            'The delete operation is forbidden. Reason: '
            . 'At least one of the fields First name, Last name, Emails or Phones must be defined.',
            $realResponse->message
        );
        $this->assertNotEmpty($realResponse->errors->errors);
    }

    public function testDeleteEmailSuccess()
    {
        $contactEmail = $this->getReference('ContactEmail_Single_'. LoadContactEmailData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $contactEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_contact_email', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
