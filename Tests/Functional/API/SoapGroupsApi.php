<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Iterator;

class SoapGroupsApiTest extends WebTestCase
{
    /** Default value for role label */
    const DEFAULT_VALUE = 'ROLE_LABEL';

    /** @var CustomSoapClient */
    static private $clientSoap = null;

    public function setUp()
    {
        if (is_null(self::$clientSoap)) {
            $client = static::createClient();
            //get wsdl
            $client->request('GET', 'api/soap');
            $wsdl = $client->getResponse()->getContent();
            self::$clientSoap = new CustomSoapClient($wsdl, array('location' =>'soap'), $client);
        }
    }

    /**
     * @param string $request
     * @param array $response
     *
     * @dataProvider requestsApi
     */
    public function testCreateGroup($request, $response)
    {
        //if (is_null($request['roles'])) {
        //    unset($request['role']);
        //}
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
        $request['name'] .= '_Updated';
        //get role id
        $roleId = self::$clientSoap->getGroupByName($request['role']);
        $roleId = $this->classToArray($roleId);
        $result = self::$clientSoap->updateGroup($roleId['id'], $request);
        $result = $this->classToArray($result);
        $this->assertEqualsResponse($response, $result);
        $role = self::$clientSoap->getGroup($roleId['id']);
        $role = $this->classToArray($role);
        $this->assertEquals($request['label'], $role['label']);
    }

    /**
     * @depends testUpdateRole
     */
    public function testGetRoles()
    {
        //get roles
        $roles = self::$clientSoap->getGroups();
        $roles = $this->classToArray($roles);
        $this->assertEquals(5, count($roles['item']));
        foreach ($roles['item'] as $role) {
            $this->assertEquals($role['role'] . '_UPDATED', strtoupper($role['label']));
        }
    }

    /**
     * @depends testGetRoles
     */
    public function testDeleteRoles()
    {
        //get roles
        $roles = self::$clientSoap->getGroups();
        $roles = $this->classToArray($roles);
        $this->assertEquals(5, count($roles['item']));
        foreach ($roles['item'] as $role) {
            $result = self::$clientSoap->deleteGroup($role['id']);
            $this->assertTrue($result);
        }
        $roles = self::$clientSoap->getGroups();
        $roles = $this->classToArray($roles);
        $this->assertEmpty($roles);
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
            __DIR__ . DIRECTORY_SEPARATOR . 'RoleRequest',
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
