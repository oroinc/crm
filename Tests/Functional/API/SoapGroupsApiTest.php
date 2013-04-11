<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Acme\Bundle\TestsBundle\Test\ToolsAPI;
use Acme\Bundle\TestsBundle\Test\Client;

/**
 * @outputBuffering enabled
 */
class SoapGroupsApiTest extends WebTestCase
{
    /** Default value for role label */
    const DEFAULT_VALUE = 'GROUP_LABEL';

    /** @var \SoapClient */
    protected $clientSoap = null;
    static protected $hasLoaded = false;

    public function setUp()
    {
        $this->clientSoap = static::createClient(array(), ToolsAPI::generateWsseHeader());

        if (!self::$hasLoaded) {
            $this->clientSoap->startTransaction();
        }
        self::$hasLoaded = true;

        $this->clientSoap->soap(
            "http://localhost/api/soap",
            array(
                'location' => 'http://localhost/api/soap',
                'soap_version' => SOAP_1_2
            )
        );
    }

    public static function tearDownAfterClass()
    {
        Client::rollbackTransaction();
    }

    /**
     * @param string $request
     * @param array  $response
     *
     * @dataProvider requestsApi
     */
    public function testCreateGroup($request, $response)
    {
        $result = $this->clientSoap->soapClient->createGroup($request);
        $result = ToolsAPI::classToArray($result);
        ToolsAPI::assertEqualsResponse($response, $result);
    }

    /**
     * @param string $request
     * @param array  $response
     *
     * @dataProvider requestsApi
     * @depends testCreateGroup
     */
    public function testUpdateGroup($request, $response)
    {
        $this->markTestSkipped('Due to missing getGroupBy');
        $request['name'] .= '_Updated';
        //get role id
        $groupId = $this->clientSoap->soapClient->getGroupByName($request['name']);
        $groupId = ToolsAPI::classToArray($groupId);
        $result = $this->clientSoap->soapClient->updateGroup($groupId['id'], $request);
        $result = ToolsAPI::classToArray($result);
        ToolsAPI::assertEqualsResponse($response, $result);
        $group = $this->clientSoap->soapClient->getGroup($groupId['id']);
        $group = ToolsAPI::classToArray($group);
        $this->assertEquals($request['label'], $group['label']);
    }

    /**
     * @depends testUpdateGroup
     */
    public function testGetGroups()
    {
        //get roles
        $groups = $this->clientSoap->soapClient->getGroups();
        $groups = ToolsAPI::classToArray($groups);
        $this->assertEquals(5, count($groups['item']));
        foreach ($groups['item'] as $group) {
            $this->assertEquals($group['name'] . '_UPDATED', strtoupper($group['label']));
        }
    }

    /**
     * @depends testGetGroups
     */
    public function testDeleteRoles()
    {
        //get roles
        $groups = $this->clientSoap->soapClient->getGroups();
        $groups = ToolsAPI::classToArray($groups);
        $this->assertEquals(5, count($groups['item']));
        foreach ($groups['item'] as $group) {
            $result = $this->clientSoap->soapClient->deleteGroup($group['id']);
            $this->assertTrue($result);
        }
        $groups = $this->clientSoap->soapClient->getGroups();
        $groups = ToolsAPI::classToArray($groups);
        $this->assertEmpty($groups);
    }

    /**
     * Data provider for REST API tests
     *
     * @return array
     */
    public function requestsApi()
    {
        return ToolsAPI::requestsApi(__DIR__ . DIRECTORY_SEPARATOR . 'GroupRequest');
    }
}
