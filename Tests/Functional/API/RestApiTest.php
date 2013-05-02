<?php

namespace Oro\Bundle\AddressBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Iterator;

class RestApiTest extends WebTestCase
{
    /**
     * Test POST
     *
     */
    public function testPost()
    {
        $requestData = array('address' =>
            array(
                'street'      => 'Some kind st.',
                'city'        => 'Old York',
                'state'       => 'AL',
                'country'     => 'US',
                'postalCode' => '32422',
            )
        );

        $client = $this->createClient();
        $client->request(
            'POST',
            "api/rest/latest/address",
            $requestData
        );

        /** @var $result Response */
        $result = $client->getResponse();

        $this->assertJsonResponse($result, 201);

        $responseData = $result->getContent();
        $this->assertNotEmpty($responseData);
        $responseData = json_decode($responseData, true);
        $this->assertInternalType('array', $responseData);
        $this->assertArrayHasKey('id', $responseData);

        return $responseData['id'];
    }

    /**
     * Test GET
     *
     * @depends testPost
     */
    public function testGet($id)
    {
        $client = $this->createClient();
        $client->request(
            'GET',
            "api/rest/latest/addresses/" . $id
        );

        /** @var $result Response */
        $result = $client->getResponse();

        $this->assertJsonResponse($result, 200);
        $resultJson = json_decode($result->getContent(), true);

        $this->assertNotEmpty($resultJson);
        $this->assertArrayHasKey('id', $resultJson);

        $this->assertEquals($id, $resultJson['id']);
    }

    /**
     * Test PUT
     *
     * @depends testPost
     */
    public function testPut($id)
    {
        $client = $this->createClient();
        // update
        $requestData = array('address' =>
            array(
                'street'      => 'Updated street',
                'street2'      => 'street2 UP'
            )
        );

        $client->request(
            'PUT',
            'http://localhost/api/rest/latest/addresses/' . $id,
            $requestData
        );

        $result = $client->getResponse();

        $this->assertJsonResponse($result, 204);

        // open address by id
        $client->request(
            'GET',
            'http://localhost/api/rest/latest/addresses/' . $id
        );

        $result = $client->getResponse();
        $this->assertJsonResponse($result, 200);

        $result = json_decode($result->getContent(), true);

        // compare result
        foreach ($requestData['address'] as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    /**
     * Test DELETE
     *
     * @depends testPost
     */
    public function testDelete($id)
    {
        $client = $this->createClient();
        $client->request(
            'DELETE',
            'http://localhost/api/rest/latest/addresses/' . $id
        );

        /** @var $result Response */
        $result = $client->getResponse();
        $this->assertJsonResponse($result, 204);

        $client->request(
            'GET',
            'http://localhost/api/rest/latest/addresses/' . $id
        );

        $result = $client->getResponse();
        $this->assertJsonResponse($result, 404);
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
