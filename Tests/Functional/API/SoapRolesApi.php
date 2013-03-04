<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Iterator;

class SoapApiTest extends WebTestCase
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
    public function testCreateRole($request, $response)
    {
        if (is_null($request['role'])) {
            $request['role'] ='';
        }
        if (is_null($request['label'])) {
            $request['label'] = self::DEFAULT_VALUE;
        }
        $result = self::$clientSoap->createRole($request);
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
    public function testUpdateRole($request, $response)
    {
        if (is_null($request['role'])) {
            $request['role'] ='';
        }
        if (is_null($request['label'])) {
            $request['label'] = self::DEFAULT_VALUE;
        }
        $request['label'] .= '_Updated';
        //get role id
        $roleId = self::$clientSoap->getRoleByName($request['role']);
        $roleId = $this->classToArray($roleId);
        $result = self::$clientSoap->updateRole($roleId['id'], $request);
        $result = $this->classToArray($result);
        $this->assertEqualsResponse($response, $result);
        $role = self::$clientSoap->getRole($roleId['id']);
        $role = $this->classToArray($role);
        $this->assertEquals($request['label'], $role['label']);
    }

    /**
     * @depends testUpdateRole
     */
    public function testGetRoles()
    {
        //get roles
        $roles = self::$clientSoap->getRoles();
        $roles = $this->classToArray($roles);
        $roles = $this->classToArray($roles);
        $this->assertEquals(5, count($roles['item']));
        foreach ($roles['item'] as $role) {
            $this->assertEquals($role['role'] . '_UPDATED', strtoupper($role['label']));
        }
    }

    /**0.
     *
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
