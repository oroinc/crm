<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

class SoapGroupsApiTest extends \PHPUnit_Framework_TestCase
{
    /** Default value for role label */
    const DEFAULT_VALUE = 'GROUP_LABEL';

    /** @var \SoapClient */
    private static $clientSoap = null;

    public function setUp()
    {
        if (is_null(self::$clientSoap)) {
            try {
                self::$clientSoap = new \SoapClient('http://localhost.com/app_test.php/api/soap', array('trace' => 1, 'soap_version' => '1.2'));
            } catch (\SoapFault $e) {
                $this->markTestSkipped('Test skipped due to http://localhost.com is not available!');
            }
        }
    }

    public static function tearDownAfterClass()
    {
        self::$clientSoap = null;
    }

    /**
     * @param string $request
     * @param array  $response
     *
     * @dataProvider requestsApi
     */
    public function testCreateGroup($request, $response)
    {
        $result = self::$clientSoap->createGroup($request);
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
        $this->markTestIncomplete("Skipped due to getGroupByName missing!");
        $request['name'] .= '_Updated';
        //get role id
        $groupId = self::$clientSoap->getGroupByName($request['name']);
        $groupId = ToolsAPI::classToArray($groupId);
        $result = self::$clientSoap->updateGroup($groupId['id'], $request);
        $result = ToolsAPI::classToArray($result);
        ToolsAPI::assertEqualsResponse($response, $result);
        $group = self::$clientSoap->getGroup($groupId['id']);
        $group = ToolsAPI::classToArray($group);
        $this->assertEquals($request['label'], $group['label']);
    }

    /**
     * @depends testUpdateGroup
     */
    public function testGetGroups()
    {
        //get roles
        $groups = self::$clientSoap->getGroups();
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
        $groups = self::$clientSoap->getGroups();
        $groups = ToolsAPI::classToArray($groups);
        $this->assertEquals(5, count($groups['item']));
        foreach ($groups['item'] as $group) {
            $result = self::$clientSoap->deleteGroup($group['id']);
            $this->assertTrue($result);
        }
        $groups = self::$clientSoap->getGroups();
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
        return ToolsAPI::requestsApi('GroupRequest');
    }
}
