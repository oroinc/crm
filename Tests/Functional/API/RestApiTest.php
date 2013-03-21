<?php

namespace Oro\Bundle\WindowsBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Iterator;

class RestApiTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    protected static $entity;

    const AUTH_USER = "admin@example.com";
    const AUTH_PW = "admin";

    public function setUp()
    {
        $this->client = static::createClient();
    }

    protected function tearDown()
    {
        unset($this->client);
    }

    /**
     * Test POST
     */
    public function testPost()
    {
        self::$entity = array(
            'data' => array(
                'position' => '0',
                'title' => 'Some title'
            )
        );

        $this->client->request(
            'POST',
            "api/rest/latest/windows",
            self::$entity,
            array(),
            array('PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PW)
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 201);

        $resultJson = json_decode($result->getContent(), true);
        $this->assertArrayHasKey("id", $resultJson);
        $this->assertGreaterThan(0, $resultJson["id"]);

        self::$entity['id'] = $resultJson["id"];
    }

    /**
     * Test PUT
     *
     * @depends testPost
     */
    public function testPut()
    {
        $this->assertNotEmpty(self::$entity);

        self::$entity['data']['position'] = 100;

        $this->client->request(
            'PUT',
            "api/rest/latest/windows/" . self::$entity['id'],
            self::$entity,
            array(),
            array('PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PW)
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
     */
    public function testGet()
    {
        $this->assertNotEmpty(self::$entity);

        $this->client->request(
            'GET',
            "api/rest/latest/windows",
            array(),
            array(),
            array('PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PW)
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
     */
    public function testDelete($itemType)
    {
        $this->assertNotEmpty(self::$entity);

        $this->client->request(
            'DELETE',
            "api/rest/latest/windows/" . self::$entity['id'],
            array(),
            array(),
            array('PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PW)
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
     */
    public function testNotFound()
    {
        $this->assertNotEmpty(self::$entity);

        $this->client->request(
            'PUT',
            "api/rest/latest/windows/" . self::$entity['id'],
            self::$entity,
            array(),
            array('PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PW)
        );
        /** @var $result Response */
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 404);

        $this->client->restart();

        $this->client->request(
            'DELETE',
            "api/rest/latest/windows/" . self::$entity['id'],
            array(),
            array(),
            array('PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PW)
        );
        /** @var $result Response */
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 404);
    }

    /**
     * Test Unauthorized
     *
     * @depends testNotFound
     */
    public function testUnauthorized()
    {
        $this->assertNotEmpty(self::$entity);

        $requests = array(
            'GET' => "api/rest/latest/windows",
            'POST' => "api/rest/latest/windows",
            'PUT' => "api/rest/latest/windows/" . self::$entity['id'],
            'DELETE' => "api/rest/latest/windows/" . self::$entity['id']
        );

        foreach ($requests as $requestType => $url) {
            $this->client->request(
                $requestType,
                $url,
                array(),
                array(),
                array()
            );
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
     */
    public function testEmptyBody()
    {
        $this->assertNotEmpty(self::$entity);

        $requests = array(
            'POST' => "api/rest/latest/windows",
            'PUT' => "api/rest/latest/windows/" . self::$entity['id']
        );

        foreach ($requests as $requestType => $url) {
            $this->client->request(
                $requestType,
                $url,
                array(),
                array(),
                array('PHP_AUTH_USER' => self::AUTH_USER, 'PHP_AUTH_PW' => self::AUTH_PW)
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
