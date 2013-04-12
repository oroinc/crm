<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Acme\Bundle\TestsBundle\Test\ToolsAPI;
use Acme\Bundle\TestsBundle\Test\Client;

/**
 * @outputBuffering enabled
 */
class RestUsersApiTest extends WebTestCase
{

    protected $client = null;
    static protected $hasLoaded = false;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
        if (!self::$hasLoaded) {
            $this->client->startTransaction();
        }
        self::$hasLoaded = true;
    }

    public static function tearDownAfterClass()
    {
        Client::rollbackTransaction();
    }

    /**
     * @return array
     */
    public function testApiCreateUser()
    {
        $request = array(
            "profile" => array (
                "username" => 'user_' . mt_rand(),
                "email" => 'test_'  . mt_rand() . '@test.com',
                "enabled" => 'true',
                "plainPassword" => '1231231q',
                "firstName" => "firstName",
                "lastName" => "lastName",
                "rolesCollection" => array("1")
            )
        );
        $this->client->request('POST', 'http://localhost/api/rest/latest/profile', $request);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @depends testApiCreateUser
     * @param  string $request
     * @return int
     */
    public function testApiUpdateUser($request)
    {
        //get user id
        $this->client->request('GET', 'http://localhost/api/rest/latest/profiles?limit=100');
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        $userId = $this->assertEqualsUser($request, $result);
        //update profile
        $request['profile']['username'] .= '_Updated';
        unset($request['profile']['plainPassword']);
        $this->client->request('PUT', 'http://localhost/api/rest/latest/profiles' . '/' . $userId, $request);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 204);
        //open user by id
        $this->client->request('GET', 'http://localhost/api/rest/latest/profiles' . '/' . $userId);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 200);

        $result = json_decode($result->getContent(), true);
        //compare result
        $this->assertEquals($request['profile']['username'], $result['username']);

        return $userId;
    }

    /**
     * @depends testApiUpdateUser
     * @param int $userId
     */
    public function testApiDeleteUser($userId)
    {
        $this->client->request('DELETE', 'http://localhost/api/rest/latest/profiles' . '/' . $userId);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 204);
        $this->client->request('GET', 'http://localhost/api/rest/latest/profiles' . '/' . $userId);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 404);
    }

    /**
     * Test API response status
     *
     * @param string $response
     * @param int    $statusCode
     */
    protected function assertJsonResponse($response, $statusCode = 201)
    {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $response->getContent()
        );
    }

    /**
     * Check created user
     *
     * @return int
     * @param array $result
     * @param array $request
     */
    protected function assertEqualsUser($request, $result)
    {
        $flag = 1;
        foreach ($result as $key => $object) {
            foreach ($request as $user) {
                if ($user['username'] == $result[$key]['username']) {
                    $flag = 0;
                    $userId = $result[$key]['id'];
                    break 2;
                }
            }
        }
        $this->assertEquals(0, $flag);
        return $userId;
    }
}
