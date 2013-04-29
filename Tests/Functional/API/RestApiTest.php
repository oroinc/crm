<?php

namespace Oro\Bundle\AddressBundle\Tests\Functional\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Iterator;

use Oro\Bundle\AddressBundle\Entity\Region;

class RestApiTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /** @var \Doctrine\ORM\EntityManager  */
    protected $em;

    protected static $id;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->em = static::$kernel->getContainer()->get('doctrine');
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
        /** @var \Oro\Bundle\AddressBundle\Entity\Region $region */
        $region = $this->em->getRepository('OroAddressBundle:Region')->findOneByCode('TEST');

        if (!$region) {
            $country = $this->em->getRepository('OroAddressBundle:Country')->findOneByIso2Code('US');

            $region = new Region();
            $region->setCode('TEST')
                ->setCountry($country)
                ->setName('TEST');
        }
        $requestData = array('address' =>
            array(
                'street'      => 'Some kind sd',
                'city'        => 'Old York',
                'state'       => 'CA',
                'country'     => 'US',
                'postalCode' => '32422',
            )
        );

        $this->client->request(
            'POST',
            "api/rest/latest/address",
            $requestData
        );


        /** @var $result Response */
        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 201);
        $this->assertRegExp('#/addresses/[\d+]#', $result->headers->get('Location'));


        preg_match('#/addresses/([\d]+)#', $result->headers->get('Location'), $match);
        $this->assertArrayHasKey(1, $match);

        self::$id = $match[1];
    }

    /**
     * Test GET
     *
     * @depends testPost
     */
    public function testGet()
    {
        $this->client->request(
            'GET',
            "api/rest/latest/addresses/".self::$id
        );

        /** @var $result Response */
        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 200);
        $resultJson = json_decode($result->getContent(), true);

        $this->assertNotEmpty($resultJson);
        $this->assertArrayHasKey('id', $resultJson);

        $this->assertEquals($resultJson['id'], self::$id);
    }

    /**
     * Test PUT
     *
     * @depends testPost
     */
    public function testPut()
    {
        $this->client->request(
            'GET',
            'http://localhost/api/rest/latest/addresses?limit=1'
        );

        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 200);

        $result = json_decode($result->getContent(), true);
        $this->assertGreaterThan(0, count($result));
        $this->assertArrayHasKey('id', $result[0]);

        $addressId = $result[0]['id'];

        // update
        $request = array('address' => $result[0]);
        $request['address']['street'] .= '_Updated!!!';
        $request['address']['country'] = 'US';
        unset($request['address']['created']);
        unset($request['address']['updated']);

        $this->client->request(
            'PUT',
            'http://localhost/api/rest/latest/addresses/' . $addressId,
            $request
        );

        $result = $this->client->getResponse();

        $this->assertJsonResponse($result, 204);

        // open address by id
        $this->client->request(
            'GET',
            'http://localhost/api/rest/latest/addresses/' . $addressId
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 200);

        $result = json_decode($result->getContent(), true);

        // compare result
        $this->assertEquals($request['address']['street'], $result['street']);
    }

    /**
     * Test DELETE
     *
     * @depends testPost
     */
    public function testDelete()
    {
        $addressId = self::$id;

        $this->client->request(
            'DELETE',
            'http://localhost/api/rest/latest/addresses/' . $addressId
        );

        /** @var $result Response */
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 204);

        $this->client->request(
            'GET',
            'http://localhost/api/rest/latest/addresses/' . $addressId
        );

        $result = $this->client->getResponse();
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
