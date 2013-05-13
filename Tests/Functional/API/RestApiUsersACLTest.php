<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Acme\Bundle\TestsBundle\Test\ToolsAPI;
use Acme\Bundle\TestsBundle\Test\Client;

/**
 * @outputBuffering enabled
 */
class RestApiUsersACLTest extends WebTestCase
{
    const USER_NAME = 'user_wo_permissions';
    const USER_PASSWORD = 'user_api_key';

    const DEFAULT_USER_ID = '1';

    /**
     * @var Client
     */
    protected $client = null;

    protected static $hasLoaded = false;

    public function setUp()
    {
        $this->client = static::createClient(
            array(),
            ToolsAPI::generateWsseHeader(self::USER_NAME, self::USER_PASSWORD)
        );
        if (!self::$hasLoaded) {
            //rebuild indexes before tests
            $kernel = $this->client->getKernel();
            $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
            $application->setAutoExit(false);
            $options = array('command' => 'oro:search:reindex');
            $options['--env'] = "test";
            $options['--quiet'] = null;
            $application->run(new \Symfony\Component\Console\Input\ArrayInput($options));

            $this->client->startTransaction();
            $this->client->appendFixtures(__DIR__ . DIRECTORY_SEPARATOR . 'DataFixtures');
        }
        self::$hasLoaded = true;
    }

    public static function tearDownAfterClass()
    {
        Client::rollbackTransaction();
    }

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
        ToolsAPI::assertJsonResponse($result, 403);
    }

    public function testApiGetUsers()
    {
        //get user id
        $this->client->request('GET', 'http://localhost/api/rest/latest/profiles?limit=100');
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }

    public function testApiGetUser()
    {
        //open user by id
        $this->client->request('GET', 'http://localhost/api/rest/latest/profiles' . '/' . self::DEFAULT_USER_ID);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }

    public function testApiUpdateUser()
    {
        $request = array(
            "profile" => array (
                "username" => 'user_' . mt_rand(),
                "email" => 'test_'  . mt_rand() . '@test.com',
                "enabled" => 'true',
                "firstName" => "firstName",
                "lastName" => "lastName",
                "rolesCollection" => array("1")
            )
        );

        $this->client->request(
            'PUT',
            'http://localhost/api/rest/latest/profiles' . '/' . self::DEFAULT_USER_ID,
            $request
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }

    public function testApiDeleteUser()
    {
        $this->client->request('DELETE', 'http://localhost/api/rest/latest/profiles' . '/' . self::DEFAULT_USER_ID);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 403);
    }
}
