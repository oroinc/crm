<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

class SoapUsersApiTest extends \PHPUnit_Framework_TestCase
{
    /** Default value for role label */
    const DEFAULT_VALUE = 'USER_LABEL';

    /** @var \SoapClient */
    static private $clientSoap = null;

    public function setUp()
    {
        if (is_null(self::$clientSoap)) {
            try {
                self::$clientSoap = @new \SoapClient('http://localhost.com/app_test.php/api/soap', array('trace' => 1));
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
    public function testCreateUser($request, $response)
    {
        $result = self::$clientSoap->createUser($request);
        $result = ToolsAPI::classToArray($result);
        ToolsAPI::assertEqualsResponse($response, $result, self::$clientSoap->__getLastResponse());
    }

    /**
     * @param string $request
     * @param array $response
     *
     * @dataProvider requestsApi
     * @depends testCreateUser
     */
    public function testUpdateUser($request, $response)
    {
        //get user id
        $userId = self::$clientSoap->getUserBy(array('item' => array('key' =>'username', 'value' =>$request['username'])));
        $userId = ToolsAPI::classToArray($userId);

        $request['username'] .= '_Updated';
        $request['email'] .= '_Updated';
        unset($request['plainPassword']);
        $result = self::$clientSoap->updateUser($userId['id'], $request);
        $result = ToolsAPI::classToArray($result);
        ToolsAPI::assertEqualsResponse($response, $result);
        $user = self::$clientSoap->getUser($userId['id']);
        $user = ToolsAPI::classToArray($user);
        $this->assertEquals($request['username'], $user['username']);
        $this->assertEquals($request['email'], $user['email']);
    }

    /**
     * @dataProvider requestsApi
     * @depends testUpdateUser
     */
    public function testGetUsers($request, $response)
    {
        $users = self::$clientSoap->getUsers(1, 1000);
        $users = ToolsAPI::classToArray($users);
        $result = false;
        foreach ($users as $user) {
            foreach ($user as $userDetails) {
                $result = $userDetails['username'] == $request['username'] . '_Updated';
                if ($result) {
                    break;
                }
            }
        }
        $this->assertTrue($result);
    }

    /**
     * @dataProvider requestsApi
     * @depends testGetUsers
     */
    public function testDeleteUser($request)
    {
        //get user id
        $userId = self::$clientSoap->getUserBy(array('item' => array('key' =>'username', 'value' =>$request['username'] . '_Updated')));
        $userId = ToolsAPI::classToArray($userId);
        $result = self::$clientSoap->deleteUser($userId['id']);
        $this->assertTrue($result);
        try {
            self::$clientSoap->getUserBy(array('item' => array('key' =>'username', 'value' =>$request['username'] . '_Updated')));
        } catch (\SoapFault $e) {
            if ($e->faultcode != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    /**
     * Data provider for REST API tests
     *
     * @return array
     */
    public function requestsApi()
    {
        return ToolsAPI::requestsApi('UserRequest');
    }
}
