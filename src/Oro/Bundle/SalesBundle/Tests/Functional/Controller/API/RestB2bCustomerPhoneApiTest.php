<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller\API;

use Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData;
use Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerPhoneData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestB2bCustomerPhoneApiTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadB2bCustomerPhoneData::class]);
    }

    public function testCreateCustomerPhone()
    {
        $customer = $this->getReference('B2bCustomer_'. LoadB2bCustomerEntitiesData::THIRD_ENTITY_NAME);
        $content = json_encode([
            'entityId' => $customer->getId(),
            'phone' => '111',
            'primary' => true
        ], JSON_THROW_ON_ERROR);
        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_phone'), [], [], [], $content);
        $customer = $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_CREATED);

        $this->assertArrayHasKey('id', $customer);
        $this->assertNotEmpty($customer['id']);
    }

    public function testCreateSecondPrimaryPhone()
    {
        $customer = $this->getReference('B2bCustomer_'. LoadB2bCustomerEntitiesData::THIRD_ENTITY_NAME);

        $content = json_encode([
            'entityId' => $customer->getId(),
            'phone' =>'test1@test.test',
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyCustomerId()
    {
        $content = json_encode([
            'phone' =>'test@test.test',
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testEmptyPhone()
    {
        $customer = $this->getReference('B2bCustomer_'. LoadB2bCustomerEntitiesData::THIRD_ENTITY_NAME);
        $content = json_encode([
            'entityId' => $customer->getId(),
            'primary' => true
        ], JSON_THROW_ON_ERROR);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_BAD_REQUEST);
    }

    public function testDeletePhoneForbidden()
    {
        $customerPhone = $this
            ->getReference('B2bCustomerPhone_Several_'. LoadB2bCustomerPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $customerPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_b2bcustomer_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_FORBIDDEN);
        $realResponse = json_decode($this->client->getResponse()->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(403, $realResponse->code);
        $this->assertEquals(
            'The delete operation is forbidden. Reason: '
            . 'Phone number was not deleted, the B2B customer has '
            . 'more than one phone number, can\'t set the new primary.',
            $realResponse->message
        );
    }

    public function testDeletePhoneSuccess()
    {
        $customerPhone = $this->getReference('B2bCustomerPhone_Single_'. LoadB2bCustomerPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $customerPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_b2bcustomer_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
