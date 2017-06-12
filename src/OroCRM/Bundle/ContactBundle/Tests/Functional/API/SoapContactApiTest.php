<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @group soap
 */
class SoapContactApiTest extends WebTestCase
{
    /**
     * @var array
     */
    protected static $contactIds = array();

    protected function setUp()
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());
        $this->initSoapClient();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::$contactIds = null;
    }

    /**
     * @param array $request
     * @dataProvider contactsDataProvider
     */
    public function testCreateContact(array $request)
    {
        $result = $this->soapClient->createContact($request);
        $this->assertInternalType('int', $result);
        $this->assertGreaterThan(0, $result, $this->soapClient->__getLastResponse());

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
     * @dataProvider contactsDataProvider
     * @depends testCreateContact
     */
    public function testGetContact(array $request)
    {
        $contactId = $this->getContactIdByFirstName($request['firstName']);

        // test getContact
        $contact = $this->soapClient->getContact($contactId);
        $contact = $this->valueToArray($contact);

        $this->assertNotEmpty($contact);
        $this->assertArrayHasKey('firstName', $contact);
        $this->assertEquals($request['firstName'], $contact['firstName']);

        // get getContacts
        $contacts = $this->soapClient->getContacts(1, 1000);
        $contacts = $this->valueToArray($contacts);
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
     * @dataProvider contactsDataProvider
     * @depends testCreateContact
     */
    public function testUpdateContact(array $request)
    {
        $contactId = $this->getContactIdByFirstName($request['firstName']);

        $request['description'] .= '_Updated';
        $result = $this->soapClient->updateContact($contactId, $request);
        $this->assertTrue($result);

        $contact = $this->soapClient->getContact($contactId);
        $contact = $this->valueToArray($contact);
        $this->assertArrayHasKey('description', $contact);
        $this->assertEquals($request['description'], $contact['description']);
    }

    /**
     * @param array $request
     * @dataProvider contactsDataProvider
     * @depends testCreateContact
     */
    public function testDeleteContact(array $request)
    {
        $contactId = $this->getContactIdByFirstName($request['firstName']);

        $result = $this->soapClient->deleteContact($contactId);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $contactId . '" can not be found');

        $this->soapClient->getContact($contactId);
    }

    /**
     * @return array
     */
    public function contactsDataProvider()
    {
        return $this->getApiRequestsData(__DIR__ . DIRECTORY_SEPARATOR . 'ContactRequest');
    }
}
