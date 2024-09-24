<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller\Api;

use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RestOpportunityTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateWsseAuthHeader()
        );

        $this->loadFixtures([LoadSalesBundleFixtures::class]);
    }

    public function testPostOpportunity(): array
    {
        $request = [
            'opportunity' => [
                'name'                => 'opportunity_name_' . random_int(1, 500),
                'owner'               => '1',
                'contact'             => $this->getReference('default_contact')->getId(),
                'status'              => 'in_progress',
                'customerAssociation' => '{"value":"Account"}', //create with new Account
            ],
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_opportunity'),
            $request
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $request['id'] = $result['id'];

        return $request;
    }

    /**
     * @depends testPostOpportunity
     */
    public function testGetOpportunity(array $request): array
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_opportunity', ['id' => $request['id']])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['opportunity']['name'], $result['name']);
        // Because api return name of status, that can be different, assert id
        $this->assertEquals('in_progress', $this->getStatusByLabel($result['status'])->getInternalId());
        // Incomplete CRM-816
        //$this->assertEquals($request['opportunity']['owner'], $result['owner']['id']);

        return $request;
    }

    /**
     * @depends testGetOpportunity
     */
    public function testPutOpportunity(array $request): array
    {
        $request['opportunity']['name'] .= '_updated';

        $this->client->request(
            'PUT',
            $this->getUrl('oro_api_put_opportunity', ['id' => $request['id']]),
            $request
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_opportunity', ['id' => $request['id']])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['opportunity']['name'], $result['name']);
        // Because api return name of status, that can be different, assert id
        $this->assertEquals('in_progress', $this->getStatusByLabel($result['status'])->getInternalId());

        return $request;
    }

    /**
     * @depends testPutOpportunity
     */
    public function testGetOpportunities(array $request)
    {
        $baseUrl = $this->getUrl('oro_api_get_opportunities');
        $this->client->request('GET', $baseUrl);

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($result);

        $result = end($result);
        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['opportunity']['name'], $result['name']);
        // Because api return name of status, that can be different, assert id
        $this->assertEquals('in_progress', $this->getStatusByLabel($result['status'])->getInternalId());

        $this->client->request('GET', $baseUrl . '?contactId=' . $request['opportunity']['contact']);
        $this->assertCount(1, $this->getJsonResponseContent($this->client->getResponse(), 200));

        $this->client->request('GET', $baseUrl . '?contactId<>' . $request['opportunity']['contact']);
        $this->assertEmpty($this->getJsonResponseContent($this->client->getResponse(), 200));
    }

    /**
     * @depends testPutOpportunity
     */
    public function testDeleteOpportunity(array $request)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_opportunity', ['id' => $request['id']])
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_opportunity', ['id' => $request['id']])
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }

    private function getStatusByLabel(string $statusLabel): EnumOptionInterface
    {
        return $this->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getRepository(EnumOption::class)
            ->findOneBy(['name' => $statusLabel, 'enumCode' => 'opportunity_status']);
    }
}
