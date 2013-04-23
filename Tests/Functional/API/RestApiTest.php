<?php

namespace Oro\Bundle\AddressBundle\Tests\Functional\API;

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
     * Test POST
     *
     */
    public function testPost()
    {
        $requestData = array('address' =>
            array(
                'street'      => 'Some kind sd',
                'city'        => 'Old York',
                'state'       => 'OY',
                'country'     => 'USA',
                'postal_code' => '32422',
            )
        );

        $this->client->request(
            'POST',
            "api/rest/latest/address",
            $requestData,
            array()
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        var_dump($result->getContent());

        $this->assertJsonResponse($result, 201);

        $resultJson = json_decode($result->getContent(), true);
        $this->assertArrayHasKey("id", $resultJson);
        $this->assertGreaterThan(0, $resultJson["id"]);

        $requestData['id'] = $resultJson["id"];
    }

    /**
     * Test GET
     *
     */
    public function testGet()
    {
        $requestData = array('address' =>
            array(
                'id'          => 6,
            )
        );

        $this->client->request(
            'GET',
            "api/rest/latest/address/" . $requestData['address']['id']
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 200);
        $resultJson = json_decode($result->getContent(), true);
        $this->assertNotEmpty($resultJson);
        $this->assertArrayHasKey('id', $resultJson[0]);
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
