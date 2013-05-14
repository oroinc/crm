<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Acme\Bundle\TestsBundle\Test\ToolsAPI;
use Acme\Bundle\TestsBundle\Test\Client;

/**
 * @outputBuffering enabled
 */
class RestInvalidUsersTest extends WebTestCase
{

    const USER_NAME = 'user_wo_permissions';
    const USER_PASSWORD = 'no_key';

    protected $client = null;

    public function testInvalidKey()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader(ToolsAPI::USER_NAME, self::USER_PASSWORD));

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
        ToolsAPI::assertJsonResponse($result, 401);
    }

    public function testInvalidUser()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader(self::USER_NAME, ToolsAPI::USER_PASSWORD));

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
        ToolsAPI::assertJsonResponse($result, 401);
    }
}
