<?php
namespace Oro\Bundle\UserBundle\Tests\Functional\API;

class SoapRolesApiTest extends \PHPUnit_Framework_TestCase
{
    /** Default value for role label */
    const DEFAULT_VALUE = 'ROLE_LABEL';

    /** @var \SoapClient */
    protected static $clientSoap = null;

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
    public function testCreateRole($request, $response)
    {
        if (is_null($request['role'])) {
            $request['role'] ='';
        }
        if (is_null($request['label'])) {
            $request['label'] = self::DEFAULT_VALUE;
        }
        $result =  self::$clientSoap->createRole($request);
        $result = ToolsAPI::classToArray($result);
        ToolsAPI::assertEqualsResponse($response, $result);
    }

    /**
     * @param string $request
     * @param array  $response
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
        $roleId =  self::$clientSoap->getRoleByName($request['role']);
        $roleId = ToolsAPI::classToArray($roleId);
        $result =  self::$clientSoap->updateRole($roleId['id'], $request);
        $result = ToolsAPI::classToArray($result);
        ToolsAPI::assertEqualsResponse($response, $result);
        $role =  self::$clientSoap->getRole($roleId['id']);
        $role = ToolsAPI::classToArray($role);
        $this->assertEquals($request['label'], $role['label']);
    }

    /**
     * @depends testUpdateRole
     * @return array
     */
    public function testGetRole()
    {
        //get roles
        $roles =  self::$clientSoap->getRoles();
        $roles = ToolsAPI::classToArray($roles);
        //filter roles
        $roles = array_filter(
            $roles['item'],
            function ($v) {
                return $v['role']. '_UPDATED' == strtoupper($v['label']);
            }
        );
        $this->assertEquals(3, count($roles));

        return $roles;
    }

    /**
     * @depends testGetRole
     * @param array $roles
     */
    public function testDeleteRoles($roles)
    {
        //get roles
        foreach ($roles as $role) {
            $result =  self::$clientSoap->deleteRole($role['id']);
            $this->assertTrue($result);
        }
        $roles =  self::$clientSoap->getRoles();
        $roles = ToolsAPI::classToArray($roles);
        if (!empty($roles)) {
            $roles = array_filter(
                $roles['item'],
                function ($v) {
                    return $v['role']. '_UPDATED' == strtoupper($v['label']);
                }
            );
        }
        $this->assertEmpty($roles);
    }

    /**
     * Data provider for REST API tests
     *
     * @return array
     */
    public function requestsApi()
    {
        return ToolsAPI::requestsApi('RoleRequest');
    }
}
