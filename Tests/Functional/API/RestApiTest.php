<?php

namespace Oro\Bundle\NavigationBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Iterator;

use Acme\Bundle\TestsBundle\Test\ToolsAPI;

class RestApiTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    protected static $entities;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        unset($this->client);
    }

    /**
     * Data provider
     * @return array
     */
    public static function navagationItemsProvider()
    {
        return array(
            array('pinbar'),
            array('favorite'),
        );
    }

    /**
     * Test POST
     *
     * @dataProvider navagationItemsProvider
     */
    public function testPost($itemType)
    {
        self::$entities[$itemType] = array(
            'url' => 'http://url.com',
            'title' => 'Title',
            'position' => 0,
            'type' => $itemType
        );

        $this->client->request(
            'POST',
            'api/rest/latest/navigationitems/' . $itemType,
            self::$entities[$itemType],
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 201);

        $resultJson = json_decode($result->getContent(), true);
        $this->assertArrayHasKey("id", $resultJson);
        $this->assertGreaterThan(0, $resultJson["id"]);

        self::$entities[$itemType]['id'] = $resultJson["id"];
    }

    /**
     * Test PUT
     *
     * @depends testPost
     * @dataProvider navagationItemsProvider
     */
    public function testPut($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $updatedPintab = array(
            'position' => 100
        );

        $this->client->request(
            'PUT',
            'api/rest/latest/navigationitems/' . $itemType . '/ids/' . self::$entities[$itemType]['id'],
            $updatedPintab,
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 200);

        $resultJson = json_decode($result->getContent(), true);
        $this->assertCount(0, $resultJson);
    }

    /**
     * Test GET
     *
     * @depends testPut
     * @dataProvider navagationItemsProvider
     */
    public function testGet($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $this->client->request(
            'GET',
            'api/rest/latest/navigationitems/' . $itemType,
            array(),
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 200);
        $resultJson = json_decode($result->getContent(), true);
        $this->assertNotEmpty($resultJson);
        $this->assertArrayHasKey('id', $resultJson[0]);
    }

    /**
     * Test DELETE
     *
     * @depends testPut
     * @dataProvider navagationItemsProvider
     */
    public function testDelete($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $this->client->request(
            'DELETE',
            'api/rest/latest/navigationitems/' . $itemType . '/ids/' . self::$entities[$itemType]['id'],
            array(),
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 204);
        $this->assertEmpty($result->getContent());
    }

    /**
     * Test 404
     *
     * @depends testDelete
     * @dataProvider navagationItemsProvider
     */
    public function testNotFound($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $this->client->request(
            'PUT',
            'api/rest/latest/navigationitems/' . $itemType . '/ids/' . self::$entities[$itemType]['id'],
            self::$entities[$itemType],
            array(),
            ToolsAPI::generateWsseHeader()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 404);

        $this->client->restart();

        $this->client->request(
            'DELETE',
            'api/rest/latest/navigationitems/' . $itemType . '/ids/' . self::$entities[$itemType]['id'],
            array(),
            array(),
            ToolsAPI::generateWsseHeader()
        );
        /** @var $result Response */
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 404);
    }

    /**
     * Test Unauthorized
     *
     * @depends testNotFound
     * @dataProvider navagationItemsProvider
     */
    public function testUnauthorized($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $requests = array(
            'GET' => "api/rest/latest/navigationitems/" . $itemType,
            'POST' => "api/rest/latest/navigationitems/" . $itemType,
            'PUT' => "api/rest/latest/navigationitems/" . $itemType . "/ids/" . self::$entities[$itemType]['id'],
            'DELETE' => "api/rest/latest/navigationitems/" . $itemType . "/ids/" . self::$entities[$itemType]['id']
        );

        foreach ($requests as $requestType => $url) {
            $this->client->request($requestType, $url);

            /** @var $result Response */
            $response = $this->client->getResponse();

            $this->assertEquals(401, $response->getStatusCode());

            $this->client->restart();
        }
    }

    /**
     * Test Empty Body error
     *
     * @depends testNotFound
     * @dataProvider navagationItemsProvider
     */
    public function testEmptyBody($itemType)
    {
        $this->assertNotEmpty(self::$entities[$itemType]);

        $requests = array(
            'POST' => "api/rest/latest/navigationitems/" . $itemType,
            'PUT' => "api/rest/latest/navigationitems/" . $itemType . "/ids/" . self::$entities[$itemType]['id'],
        );

        foreach ($requests as $requestType => $url) {
            $this->client->request(
                $requestType,
                $url,
                array(),
                array(),
                ToolsAPI::generateWsseHeader()
            );

            /** @var $response Response */
            $response = $this->client->getResponse();
            $this->assertJsonResponse($response, 400);

            $responseJson = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('message', $responseJson);
            $this->assertEquals("Wrong JSON inside POST body", $responseJson['message']);

            $this->client->restart();
        }
    }

    /**
     * Test API response status
     *
     * @param Response $response
     * @param int $statusCode
     */
    protected function assertJsonResponse($response, $statusCode = 200)
    {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json')
        );
    }
}
