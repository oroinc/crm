<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\API;

use FOS\RestBundle\Util\Codes;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadLeadPhoneData;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestLeadPhoneApiTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([
            'OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadLeadPhoneData'
        ]);
    }

    public function testCreateLeadPhone()
    {
        $lead = $this->getReference('third_lead');
        $content = json_encode([
            'leadId' => $lead->getId(),
            'phone' => '111',
            'primary' => true
        ]);
        $this->client->request('POST', $this->getUrl('oro_api_post_lead_phone'), [], [], [], $content);
        $lead = $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_CREATED);

        $this->assertArrayHasKey('id', $lead);
        $this->assertNotEmpty($lead['id']);
    }

    public function testCreateSecondPrimaryPhone()
    {
        $lead = $this->getReference('default_lead');

        $content = json_encode([
            'leadId' => $lead->getId(),
            'phone' =>'test1@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_lead_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyLeadId()
    {
        $lead = json_encode([
            'phone' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_lead_phone'), [], [], [], $lead);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyPhone()
    {
        $lead = $this->getReference('third_lead');
        $content = json_encode([
            'leadId' => $lead->getId(),
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_lead_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testDeletePhoneForbidden()
    {
        $leadPhone = $this->getReference('LeadPhone_Several_'. LoadLeadPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $leadPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_lead_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(
            '{"code":500,"message":"orocrm.lead.phone.error.delete.more_one"}',
            $this->client->getResponse()->getContent()
        );
    }

    public function testDeletePhoneSuccess()
    {
        $leadPhone = $this->getReference('LeadPhone_Single_'. LoadLeadPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $leadPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_lead_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
