<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller\API;

use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestLeadEmailApiTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadSalesBundleFixtures::class]);
    }

    public function testCreateLeadEmail()
    {
        $lead = $this->getReference('third_lead');
        $content = json_encode([
            'entityId' => $lead->getId(),
            'email' => 'test@test.test',
            'primary' => false
        ], JSON_THROW_ON_ERROR);
        $this->client->request('POST', $this->getUrl('oro_api_post_leademail'), [], [], [], $content);
        $lead = $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_CREATED);

        $this->assertArrayHasKey('id', $lead);
        $this->assertNotEmpty($lead['id']);
    }

    public function testCreateSecondPrimaryEmail()
    {
        $lead = $this->getReference('default_lead');

        $content = json_encode([
            'leadId' => $lead->getId(),
            'email' =>'test1@test.test',
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_leademail'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyLeadId()
    {
        $lead = json_encode([
            'email' =>'test@test.test',
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_leademail'), [], [], [], $lead);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyEmail()
    {
        $lead = $this->getReference('third_lead');
        $content = json_encode([
            'leadId' => $lead->getId(),
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_leademail'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testDeleteEmailForbidden()
    {
        /** @var Lead $lead */
        $lead = $this->getReference('third_lead');
        $leadEmailId = null;
        $this->assertCount(2, $lead->getEmails());
        foreach ($lead->getEmails() as $leadEmail) {
            if ($leadEmail->isPrimary()) {
                $leadEmailId = $leadEmail->getId();
                break;
            }
        }
        $this->assertNotNull($leadEmailId);
        $routeParams = [
            'id' => $leadEmailId
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_leademail', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        $realResponse = json_decode($this->client->getResponse()->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(403, $realResponse->code);
        $this->assertEquals(
            'The delete operation is forbidden. Reason: '
            . 'Email address was not deleted, the lead has '
            . 'more than one email addresses, can\'t set the new primary.',
            $realResponse->message
        );
    }

    public function testDeleteEmailSuccess()
    {
        /** @var Lead $lead */
        $lead = $this->getReference('default_lead');
        $leadEmailId = $lead->getEmails()->first()->getId();
        $routeParams = [
            'id' => $leadEmailId
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_leademail', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
