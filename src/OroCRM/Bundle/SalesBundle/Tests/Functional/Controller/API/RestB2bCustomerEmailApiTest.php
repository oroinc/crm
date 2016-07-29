<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Controller\API;

use FOS\RestBundle\Util\Codes;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEmailData;
use OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestB2bCustomerEmailApiTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([
            'OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEmailData'
        ]);
    }

    public function testCreateB2bCustomerEmail()
    {
        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::THIRD_ENTITY_NAME);
        $content = json_encode([
            'entityId' => $customer->getId(),
            'email' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_email'), [], [], [], $content);
        $customer = $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_CREATED);

        $this->assertArrayHasKey('id', $customer);
        $this->assertNotEmpty($customer['id']);
    }

    public function testCreateSecondPrimaryEmail()
    {
        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);
        $content = json_encode([
            'entityId' => $customer->getId(),
            'email' =>'test1@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyB2bCustomerId()
    {
        $content = json_encode([
            'email' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyEmail()
    {
        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);
        $content = json_encode([
            'entityId' => $customer->getId(),
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testDeleteEmailForbidden()
    {
        $customerEmail = $this->getReference('B2bCustomerEmail_Several_'. LoadB2bCustomerEmailData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $customerEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_b2bcustomer_email', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(
            '{"code":500,"message":"orocrm.sales.email.error.delete.more_one"}',
            $this->client->getResponse()->getContent()
        );
    }

    public function testDeleteEmailSuccess()
    {
        $customerEmail = $this->getReference('B2bCustomerEmail_Single_'. LoadB2bCustomerEmailData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $customerEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_b2bcustomer_email', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
