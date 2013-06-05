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
    public $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
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
                    "description" => 'Contact description'
                )
            )
        );
        $this->client->request('POST', 'http://localhost/api/rest/latest/contact', $request);
        $result = $this->client->getResponse();
        //we can get Id
        $id = json_decode($result->getContent(), true);
        ToolsAPI::assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @param $request
     * @depends testCreateContact
     * @return array
     */
    public function testGetContact($request)
    {
        $this->client->request('GET', 'http://localhost/api/rest/latest/contacts');
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        $flag = 1;
        foreach ($result as $contact) {
            if ($contact['attributes']['first_name']['value'] == $request['contact']['attributes']['first_name']) {
                $flag = 0;
                break;
            }
        }
        $this->assertEquals(0, $flag);

        $this->client->request('GET', 'http://localhost/api/rest/latest/contacts' . '/' . $contact['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        return $contact;
    }

    /**
     * @param $contact
     * @param $request
     * @depends testGetContact
     * @depends testCreateContact
     */
    public function testUpdateContact($contact, $request)
    {
        $request['contact']['attributes']['first_name'] .= "_Updated";
        $this->client->request('PUT', 'http://localhost/api/rest/latest/contacts' . '/' . $contact['id'], $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', 'http://localhost/api/rest/latest/contacts' . '/' . $contact['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        $this->assertEquals($request['contact']['attributes']['first_name'], $result['attributes']['first_name']['value'], 'Contact does not updated');
    }

    /**
     * @param $contact
     * @depends testGetContact
     */
    public function testDeleteContact($contact)
    {
        $this->client->request('DELETE', 'http://localhost/api/rest/latest/contacts' . '/' . $contact['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', 'http://localhost/api/rest/latest/contacts' . '/' . $contact['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
