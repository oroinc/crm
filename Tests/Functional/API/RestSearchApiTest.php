<?php

namespace Oro\Bundle\SearchBundle\Tests\Functional\API;

use Acme\Bundle\TestsBundle\Test\WebTestCase;
use Acme\Bundle\TestsBundle\Test\ToolsAPI;
use Acme\Bundle\TestsBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestSearchApiTest extends WebTestCase
{
    protected $client = null;
    static protected $hasLoaded = false;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
        if (!self::$hasLoaded) {
            $this->client->appendFixtures(__DIR__ . DIRECTORY_SEPARATOR . 'DataFixtures');
        }
        self::$hasLoaded = true;
    }

    /**
     * @param array $request
     * @param array $response
     *
     * @dataProvider requestsApi
     */
    public function testApi($request, $response)
    {
        $requestUrl = '';
        foreach ($request as $key => $value) {
            $requestUrl .= (is_null($request[$key])) ? '' :
                (($requestUrl!=='') ? '&':'') . "{$key}=" . $value;
        }
        $this->client->request('GET', "http://localhost/api/rest/latest/search?search={$requestUrl}");

        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        //compare result
        $this->assertEqualsResponse($response, $result);
    }

    /**
     * Data provider for REST API tests
     *
     * @return array
     */
    public function requestsApi()
    {
        return ToolsAPI::requestsApi(__DIR__ . DIRECTORY_SEPARATOR . 'requests');
    }

    /**
     * Test API response status
     *
     * @param string $response
     * @param int $statusCode
     */
    protected function assertJsonResponse($response, $statusCode = 200)
    {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $response->getContent()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
        );
    }

    /**
     * Test API response
     *
     * @param array $response
     * @param array $result
     */
    protected function assertEqualsResponse($response, $result)
    {
        $this->assertEquals($response['records_count'], $result['records_count']);
        $this->assertEquals($response['count'], $result['count']);
        if (isset($response['rest']['data']) && is_array($response['rest']['data'])) {
            foreach ($response['rest']['data'] as $key => $object) {
                foreach ($object as $property => $value) {
                    $this->assertEquals($value, $result['data'][$key][$property]);
                }
            }
        }
    }
}
