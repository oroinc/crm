<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Iterator;

class SoapUsersApiTest extends \PHPUnit_Framework_TestCase
{
    /** Default value for role label */
    const DEFAULT_VALUE = 'USER_LABEL';

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
    public function testCreateUser($request, $response)
    {
        $this->markTestIncomplete("Due to bug in adding Users and attributes");
        $result = self::$clientSoap->createUser($request);
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
    public function testUpdateUser($request, $response)
    {
        $this->markTestIncomplete("Skipped due to getUserByName and getUserByEmail missing!");
        $request['name'] .= '_Updated';
        //get user id
        $userId = self::$clientSoap->getUserByName($request['name']);
        $userId = $this->classToArray($userId);
        $result = self::$clientSoap->updateUser($userId['id'], $request);
        $result = $this->classToArray($result);
        $this->assertEqualsResponse($response, $result);
        $user = self::$clientSoap->getUser($userId['id']);
        $user = $this->classToArray($user);
        $this->assertEquals($request['label'], $user['label']);
    }

    /**
     * @depends testUpdateRole
     */
    public function testGetUsers()
    {
        //get roles
        $users = self::$clientSoap->getUsers();
        $users = $this->classToArray($users);
        $this->assertEquals(5, count($users['item']));
        foreach ($users['item'] as $user) {
            $this->assertEquals($user['name'] . '_UPDATED', strtoupper($user['label']));
        }
    }

    /**
     * @depends testGetRoles
     */
    public function testDeleteUser()
    {
        //get roles
        $users = self::$clientSoap->getUsers();
        $users = $this->classToArray($users);
        $this->assertEquals(5, count($users['item']));
        foreach ($users['item'] as $user) {
            $result = self::$clientSoap->deleteUser($user['id']);
            $this->assertTrue($result);
        }
        $users = self::$clientSoap->getUsers();
        $users = $this->classToArray($users);
        $this->assertEmpty($users);
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
            __DIR__ . DIRECTORY_SEPARATOR . 'UserRequest',
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
