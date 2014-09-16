<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Controller\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestLeadTest extends WebTestCase
{
    /** @var  Channel */
    protected static $dataChannel;

    protected function setUp()
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );
        $this->loadFixtures(['OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);
    }

    protected function postFixtureLoad()
    {
        self::$dataChannel = $this->getReference('default_channel');
    }

    /**
     * @return array
     */
    public function testPostLead()
    {
        $request = [
            "lead" => [
                'name'          => 'lead_name_' . mt_rand(1, 500),
                'firstName'     => 'first_name_' . mt_rand(1, 500),
                'lastName'      => 'last_name_' . mt_rand(1, 500),
                'owner'         => '1',
                'dataChannel'   => self::$dataChannel->getId()
            ]
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_lead'),
            $request
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $request['id'] = $result['id'];

        return $request;
    }

    /**
     * @param $request
     *
     * @depends testPostLead
     * @return  mixed
     */
    public function testGetLead($request)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_lead', ['id' => $request['id']])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['lead']['firstName'], $result['firstName']);
        $this->assertEquals($request['lead']['lastName'], $result['lastName']);
        $this->assertEquals($request['lead']['name'], $result['name']);
        $this->assertEquals('New', $result['status']);
        // TODO: incomplete CRM-816
        //$this->assertEquals($request['lead']['owner'], $result['owner']['id']);
        return $request;
    }

    /**
     * @param $request
     *
     * @depends testGetLead
     * @return  mixed
     */
    public function testPutLead($request)
    {
        $request['lead']['firstName'] .= '_updated';
        $request['lead']['lastName'] .= '_updated';
        $request['lead']['name'] .= '_updated';

        $this->client->request(
            'PUT',
            $this->getUrl('oro_api_put_lead', ['id' => $request['id']]),
            $request
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_lead', ['id' => $request['id']])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['lead']['firstName'], $result['firstName']);
        $this->assertEquals($request['lead']['lastName'], $result['lastName']);
        $this->assertEquals($request['lead']['name'], $result['name']);
        $this->assertEquals('New', $result['status']);

        return $request;
    }

    /**
     * @depends testPutLead
     */
    public function testGetLeads($request)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_leads')
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($result);

        $result = end($result);
        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['lead']['firstName'], $result['firstName']);
        $this->assertEquals($request['lead']['lastName'], $result['lastName']);
        $this->assertEquals($request['lead']['name'], $result['name']);
        $this->assertEquals('New', $result['status']);
    }

    /**
     * @depends testPutLead
     */
    public function testDeleteLead($request)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_lead', ['id' => $request['id']])
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_lead', ['id' => $request['id']])
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }
}
