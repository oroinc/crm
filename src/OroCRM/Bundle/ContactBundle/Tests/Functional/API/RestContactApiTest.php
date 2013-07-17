<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestContactApiTest extends WebTestCase
{
    /** @var Client  */
    protected $client;

    /**
     * @var array
     */
    protected $testAddress = array(
        'types' => array('shipping'),
        'street' => 'contact_street',
        'city' => 'contact_city',
        'country' => 'US',
        'state' => 'US.FL',
        'postalCode' => '12345',
    );

    public function setUp()
    {
        if (!isset($this->client)) {
            $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
        } else {
            $this->client->restart();
        }
    }

    /**
     * @param array $actualAddresses
     */
    protected function assertAddresses(array $actualAddresses)
    {
        $this->assertCount(1, $actualAddresses);
        $address = current($actualAddresses);

        foreach (array('types', 'street', 'city') as $key) {
            $this->assertArrayHasKey($key, $address);
            $this->assertEquals($this->testAddress[$key], $address[$key]);
        }
    }

    /**
     * @return array
     */
    public function testCreateContact()
    {
        $request = array(
            "contact" => array (
                "attributes" => array(
                    "first_name" => 'Contact_fname_' . mt_rand(),
                    "last_name" => 'Contact_lname',
                    "name_prefix" => 'Contact name prefix',
                    "description" => 'Contact description',
                ),
                "addresses" => array($this->testAddress)
            )
        );
        $this->client->request('POST', $this->client->generate('oro_api_post_contact'), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        $contact = json_decode($result->getContent(), true);
        $this->assertArrayHasKey('id', $contact);
        $this->assertNotEmpty($contact['id']);

        return $request;
    }

    /**
     * @param $request
     * @depends testCreateContact
     * @return array
     */
    public function testGetContact($request)
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_contacts'));
        $result = $this->client->getResponse();
        $entities = json_decode($result->getContent(), true);
        $this->assertNotEmpty($entities);

        $requiredContact = null;
        foreach ($entities as $entity) {
            if ($entity['attributes']['first_name']['value'] == $request['contact']['attributes']['first_name']) {
                $requiredContact = $entity;
                break;
            }
        }
        $this->assertNotNull($requiredContact);

        $this->client->request('GET', $this->client->generate('oro_api_get_contact', array('id' => $requiredContact['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $selectedContact = json_decode($result->getContent(), true);
        $this->assertEquals($requiredContact, $selectedContact);

        $this->assertArrayHasKey('addresses', $selectedContact);
        $this->assertAddresses($selectedContact['addresses']);

        return $selectedContact;
    }

    /**
     * @param $contact
     * @param $request
     * @depends testGetContact
     * @depends testCreateContact
     */
    public function testUpdateContact($contact, $request)
    {
        $this->testAddress['types'] = array('billing');

        $request['contact']['attributes']['first_name'] .= "_Updated";
        $request['contact']['addresses'][0]['types'] = $this->testAddress['types'];
        $request['contact']['addresses'][0]['primary'] = true;

        $this->client->request('PUT', $this->client->generate('oro_api_put_contact', array('id' => $contact['id'])), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request('GET', $this->client->generate('oro_api_get_contact', array('id' => $contact['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $contact = json_decode($result->getContent(), true);
        $this->assertEquals(
            $request['contact']['attributes']['first_name'],
            $contact['attributes']['first_name']['value'],
            'Contact was not updated'
        );

        $this->assertArrayHasKey('addresses', $contact);
        $this->assertAddresses($contact['addresses']);
    }

    /**
     * @param $contact
     * @depends testGetContact
     */
    public function testDeleteContact($contact)
    {
        $this->client->request('DELETE', $this->client->generate('oro_api_delete_contact', array('id' => $contact['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request('GET', $this->client->generate('oro_api_get_contact', array('id' => $contact['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
