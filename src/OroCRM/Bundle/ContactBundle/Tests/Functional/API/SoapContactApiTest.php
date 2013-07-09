<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapContactApiTest extends WebTestCase
{
    /** @var Client */
    protected $client = null;

    public function setUp()
    {
        $this->markTestSkipped('BAP-717');
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());

        $this->client->soap(
            "http://localhost/api/soap",
            array(
                'location' => 'http://localhost/api/soap',
                'soap_version' => SOAP_1_2
            )
        );
    }

    /**
     * @param string $request
     * @param array  $response
     * @dataProvider requestsApi
     */
    public function testCreateContact($request, $response)
    {
        $result = $this->client->soapClient->createContact($request);
        $result = ToolsAPI::classToArray($result);
        ToolsAPI::assertEqualsResponse($response, $result, $this->client->soapClient->__getLastResponse());
    }

    /**
     * @param $request
     * @dataProvider requestsApi
     * @depends testCreateContact
     * @return array
     */
    public function testGetContacts($request)
    {
        $contacts = $this->client->soapClient->getContacts(1, 1000);
        $contacts = ToolsAPI::classToArray($contacts);
        $result = false;
        foreach ($contacts as $contact) {
            foreach ($contact as $contactDetails) {
                $result = $contactDetails['first_name'] == $request['first_name'];
                if ($result) {
                    break;
                }
            }
        }
        $this->assertTrue($contact);

        return $result;
    }

    /**
     * @param $request
     * @dataProvider requestsApi
     * @depends testCreateContact
     * @return $contactId
     */
    public function testUpdateContact($request)
    {
        $contacts = $this->client->soapClient->getContacts(1, 1000);
        $contacts = ToolsAPI::classToArray($contacts);
        $result = false;
        foreach ($contacts as $contact) {
            foreach ($contact as $contactDetails) {
                $result = $contactDetails['first_name'] == $request['first_name'];
                if ($result) {
                    $contactId = $contactDetails['id'];
                    break;
                }
            }
        }
        $request['attributes']['description'] .= '_Updated';
        $result = $this->client->soapClient->updateContact($contactId, $request);
        $this->assertTrue($result);
        $contact = $this->client->soapClient->getContactGroup($contactId);
        $contact = ToolsAPI::classToArray($contact);
        $result = false;
        if ($contact['attributes']['description'] == $request['attributes']['description']) {
            $result = true;
        }
        $this->assertTrue($result);

        return $contactId;
    }

    /**
     * @param $contactId
     * @depends testGetContacts
     * @throws \Exception|\SoapFault
     */
    public function testDeleteContact($contactId)
    {
        $result = $this->client->soapClient->deleteContact($contactId);
        $this->assertTrue($result);
        try {
            $this->client->soapClient->getContact($contactId);
        } catch (\SoapFault $e) {
            if ($e->faultcode != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    /**
     * Data provider for API tests
     * @return array
     */
    public function requestsApi()
    {
        return ToolsAPI::requestsApi(__DIR__ . DIRECTORY_SEPARATOR . 'ContactRequest');
    }
}
