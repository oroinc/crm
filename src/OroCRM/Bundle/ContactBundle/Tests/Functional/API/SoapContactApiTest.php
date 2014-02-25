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
    /**
     * @var array
     */
    protected static $contactIds = array();

    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
        $this->client->soap(
            "http://localhost/api/soap",
            array(
                'location' => 'http://localhost/api/soap',
                'soap_version' => SOAP_1_2
            )
        );
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::$contactIds = null;
    }

    /**
     * @param string $request
     * @dataProvider requestsApi
     */
    public function testCreateContact($request)
    {
        $result = $this->client->getSoap()->createContact($request);
        $this->assertInternalType('int', $result);
        $this->assertGreaterThan(0, $result, $this->client->getSoap()->__getLastResponse());

        self::$contactIds[$request['firstName']] = $result;
    }

    /**
     * @param string $firstName
     * @return int
     */
    protected function getContactIdByFirstName($firstName)
    {
        $this->assertArrayHasKey($firstName, self::$contactIds);
        return self::$contactIds[$firstName];
    }

    /**
     * @param array $request
     * @dataProvider requestsApi
     * @depends testCreateContact
     */
    public function testGetContact($request)
    {
        $contactId = $this->getContactIdByFirstName($request['firstName']);

        // test getContact
        $contact = $this->client->getSoap()->getContact($contactId);
        $contact = ToolsAPI::classToArray($contact);

        $this->assertNotEmpty($contact);
        $this->assertArrayHasKey('firstName', $contact);
        $this->assertEquals($request['firstName'], $contact['firstName']);

        // get getContacts
        $contacts = $this->client->getSoap()->getContacts(1, 1000);
        $contacts = ToolsAPI::classToArray($contacts);
        $contactFound = array_filter(
            $contacts,
            function ($a) use ($contactId) {
                return $a['id'] == $contactId;
            }
        );
        $this->assertNotEmpty($contactFound);
    }

    /**
     * @param array $request
     * @dataProvider requestsApi
     * @depends testCreateContact
     */
    public function testUpdateContact($request)
    {
        $contactId = $this->getContactIdByFirstName($request['firstName']);

        $request['description'] .= '_Updated';
        $result = $this->client->getSoap()->updateContact($contactId, $request);
        $this->assertTrue($result);

        $contact = $this->client->getSoap()->getContact($contactId);
        $contact = ToolsAPI::classToArray($contact);
        $this->assertArrayHasKey('description', $contact);
        $this->assertEquals($request['description'], $contact['description']);
    }

    /**
     * @param $request
     * @dataProvider requestsApi
     * @depends testCreateContact
     */
    public function testDeleteContact($request)
    {
        $contactId = $this->getContactIdByFirstName($request['firstName']);

        $result = $this->client->getSoap()->deleteContact($contactId);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $contactId . '" can not be found');

        $this->client->getSoap()->getContact($contactId);
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
