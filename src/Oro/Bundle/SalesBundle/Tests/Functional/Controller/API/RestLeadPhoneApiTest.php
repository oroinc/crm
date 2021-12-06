<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller\API;

use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadLeadPhoneData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestLeadPhoneApiTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadLeadPhoneData::class]);
    }

    public function testCreateLeadPhone()
    {
        $lead = $this->getReference('third_lead');
        $content = json_encode([
            'entityId' => $lead->getId(),
            'phone' => '111',
            'primary' => true
        ], JSON_THROW_ON_ERROR);
        $this->client->request('POST', $this->getUrl('oro_api_post_lead_phone'), [], [], [], $content);
        $lead = $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_CREATED);

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
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_lead_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyLeadId()
    {
        $lead = json_encode([
            'phone' =>'test@test.test',
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_lead_phone'), [], [], [], $lead);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyPhone()
    {
        $lead = $this->getReference('third_lead');
        $content = json_encode([
            'leadId' => $lead->getId(),
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_lead_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testDeletePhoneForbidden()
    {
        $leadPhone = $this->getReference('LeadPhone_Several_'. LoadLeadPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $leadPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_lead_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        $realResponse = json_decode($this->client->getResponse()->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(403, $realResponse->code);
        $this->assertEquals(
            'The delete operation is forbidden. Reason: '
            . 'Phone number was not deleted, the lead has '
            . 'more than one phone number, can\'t set the new primary.',
            $realResponse->message
        );
    }

    public function testDeletePhoneSuccess()
    {
        $leadPhone = $this->getReference('LeadPhone_Single_'. LoadLeadPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $leadPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_lead_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
