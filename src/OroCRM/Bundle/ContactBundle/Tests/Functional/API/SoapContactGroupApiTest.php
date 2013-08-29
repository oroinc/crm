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
    /** @var Client */
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

    /**
     * @return array
     */
    public function testCreateContactGroup()
    {
        $request = array(
            "label" => 'Group name_' . mt_rand(),
            "owner" => '1'
        );
        $result = $this->client->getSoap()->createContactGroup($request);
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
        $groups = $this->client->getSoap()->getContactGroups(1, 1000);
        $groups = ToolsAPI::classToArray($groups);
        $result = false;
        foreach ($groups as $group) {
            foreach ($group as $groupDetails) {
                $result = $groupDetails['label'] == $request['label'];
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
        $request['label'] .= '_Updated';
        $result = $this->client->getSoap()->updateContactGroup($group['id'], $request);
        $this->assertTrue($result);
        $group = $this->client->getSoap()->getContactGroup($group['id']);
        $group = ToolsAPI::classToArray($group);
        $result = false;
        if ($group['label'] == $request['label']) {
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
        $result = $this->client->getSoap()->deleteContactGroup($group['id']);
        $this->assertTrue($result);
        try {
            $this->client->getSoap()->getContactGroup($group['id']);
        } catch (\SoapFault $e) {
            if ($e->faultcode != 'NOT_FOUND') {
                throw $e;
            }
        }
    }
}
