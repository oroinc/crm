<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller\API;

use Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEmailData;
use Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestB2bCustomerEmailApiTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadB2bCustomerEmailData::class]);
    }

    public function testCreateB2bCustomerEmail()
    {
        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::THIRD_ENTITY_NAME);
        $content = json_encode([
            'entityId' => $customer->getId(),
            'email' =>'test@test.test',
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_email'), [], [], [], $content);
        $customer = $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_CREATED);

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
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyB2bCustomerId()
    {
        $content = json_encode([
            'email' =>'test@test.test',
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyEmail()
    {
        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);
        $content = json_encode([
            'entityId' => $customer->getId(),
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_email'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testDeleteEmailForbidden()
    {
        $customerEmail = $this->getReference('B2bCustomerEmail_Several_'. LoadB2bCustomerEmailData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $customerEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_b2bcustomer_email', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        $realResponse = json_decode($this->client->getResponse()->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(403, $realResponse->code);
        $this->assertEquals(
            'The delete operation is forbidden. Reason: '
            . 'Email address was not deleted, the B2B customer has '
            . 'more than one email addresses, can\'t set the new primary.',
            $realResponse->message
        );
    }

    public function testDeleteEmailSuccess()
    {
        $customerEmail = $this->getReference('B2bCustomerEmail_Single_'. LoadB2bCustomerEmailData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $customerEmail->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_b2bcustomer_email', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
