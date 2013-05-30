<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapContactGroupApiTest extends WebTestCase
{
    /** @var \SoapClient */
    protected $clientSoap = null;

    public function setUp()
    {
        $this->clientSoap = static::createClient(array(), ToolsAPI::generateWsseHeader());

        $this->clientSoap->soap(
            "http://localhost/api/soap",
            array(
                'location' => 'http://localhost/api/soap',
                'soap_version' => SOAP_1_2
            )
        );
    }

    /**
     * @return array
     */
    public function testCreateContactGroup()
    {
        $request = array(
            "name" => 'Group name_' . mt_rand()
        );
        $result = $this->clientSoap->soapClient->createContactGroup($request);
        $this->assertTrue($result);

        return $request;
    }

    /**
     * @param $request
     * @depends testCreateContactGroup
     * @return array
     */
    public function testGetContactGroups($request)
    {
        $groups = $this->clientSoap->soapClient->getContactGroups(1, 1000);
        $groups = ToolsAPI::classToArray($groups);
        $result = false;
        foreach ($groups as $group) {
            foreach ($group as $groupDetails) {
                $result = $groupDetails['name'] == $request['name'];
                if ($result) {
                    break;
                }
            }
        }
        $this->assertTrue($result);

        return $groupDetails;
    }

    /**
     * @param $request
     * @param $group
     * @depends testCreateContactGroup
     * @depends testGetContactGroups
     */
    public function testUpdateContact($request, $group)
    {
        $request['name'] .= '_Updated';
        $result = $this->clientSoap->soapClient->updateContactGroup($group['id'], $request);
        $this->assertTrue($result);
        $group = $this->clientSoap->soapClient->getContactGroup($group['id']);
        $group = ToolsAPI::classToArray($group);
        $result = false;
        if ($group['name'] == $request['name']){
            $result = true;
        }
        $this->assertTrue($result);
    }

    /**
     * @param $group
     * @depends testGetContactGroups
     * @throws \Exception|\SoapFault
     */
    public function testDeleteContactGroup($group)
    {
        $result = $this->clientSoap->soapClient->deleteContactGroup($group['id']);
        $this->assertTrue($result);
        try {
            $this->clientSoap->soapClient->getContactGroup($group['id']);
        } catch (\SoapFault $e) {
            if ($e->faultcode != 'NOT_FOUND') {
                throw $e;
            }
        }
    }
}
