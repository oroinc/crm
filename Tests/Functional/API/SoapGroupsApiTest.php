<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Iterator;

class SoapGroupsApiTest extends WebTestCase
{
    /** Default value for role label */
    const DEFAULT_VALUE = 'GROUP_LABEL';

    /** @var CustomSoapClient */
    static private $clientSoap = null;

    public function setUp()
    {
        if (is_null(self::$clientSoap)) {
            try {
                self::$clientSoap = @new \SoapClient('http://localhost.com/app_test.php/api/soap');
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
     * @param array $response
     *
     * @dataProvider requestsApi
     */
    public function testCreateGroup($request, $response)
    {
        $result = self::$clientSoap->createGroup($request);
        $result = $this->classToArray($result);
        $this->assertEqualsResponse($response, $result);
    }

    /**
     * @param string $request
     * @param array $response
     *
     * @dataProvider requestsApi
     * @depends testCreateRole
     */
    public function testUpdateGroup($request, $response)
    {
        $this->markTestIncomplete("Skipped due to getGroupByName missing!");
        $request['name'] .= '_Updated';
        //get role id
        $groupId = self::$clientSoap->getGroupByName($request['name']);
        $groupId = $this->classToArray($groupId);
        $result = self::$clientSoap->updateGroup($groupId['id'], $request);
        $result = $this->classToArray($result);
        $this->assertEqualsResponse($response, $result);
        $group = self::$clientSoap->getGroup($groupId['id']);
        $group = $this->classToArray($group);
        $this->assertEquals($request['label'], $group['label']);
    }

    /**
     * @depends testUpdateRole
     */
    public function testGetGroups()
    {
        //get roles
        $groups = self::$clientSoap->getGroups();
        $groups = $this->classToArray($groups);
        $this->assertEquals(5, count($groups['item']));
        foreach ($groups['item'] as $group) {
            $this->assertEquals($group['name'] . '_UPDATED', strtoupper($group['label']));
        }
    }

    /**
     * @depends testGetRoles
     */
    public function testDeleteRoles()
    {
        //get roles
        $groups = self::$clientSoap->getGroups();
        $groups = $this->classToArray($groups);
        $this->assertEquals(5, count($groups['item']));
        foreach ($groups['item'] as $group) {
            $result = self::$clientSoap->deleteGroup($group['id']);
            $this->assertTrue($result);
        }
        $groups = self::$clientSoap->getGroups();
        $groups = $this->classToArray($groups);
        $this->assertEmpty($groups);
    }

    /**
     * Data provider for REST API tests
     *
     * @return array
     */
    public function requestsApi()
    {
        $parameters = array();
        $testFiles = new \RecursiveDirectoryIterator(
            __DIR__ . DIRECTORY_SEPARATOR . 'GroupRequest',
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        foreach ($testFiles as $fileName => $object) {
            $parameters[$fileName] = Yaml::parse($fileName);
            if (is_null($parameters[$fileName]['response'])) {
                unset($parameters[$fileName]['response']);
            }
        }
        return
            $parameters;
    }

    /**
     * Test API response
     *
     * @param array $response
     * @param array $result
     */
    protected function assertEqualsResponse($response, $result)
    {
        $this->assertEquals($response['return'], $result);
    }

    /**
     * Convert stdClass to array
     *
     * @param $class
     * @return array
     */
    protected function classToArray($class)
    {
        return json_decode(json_encode($class), true);
    }
}
