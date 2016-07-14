<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\API;

use FOS\RestBundle\Util\Codes;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData;
use OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerPhoneData;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class RestB2bCustomerPhoneApiTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([
            'OroCRM\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerPhoneData'
        ]);
    }

    public function testCreateCustomerPhone()
    {
        $customer = $this->getReference('B2bCustomer_'. LoadB2bCustomerEntitiesData::THIRD_ENTITY_NAME);
        $content = json_encode([
            'b2bCustomerId' => $customer->getId(),
            'phone' => '111',
            'primary' => true
        ]);
        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_phone'), [], [], [], $content);
        $customer = $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_CREATED);

        $this->assertArrayHasKey('id', $customer);
        $this->assertNotEmpty($customer['id']);
    }

    public function testCreateSecondPrimaryPhone()
    {
        $customer = $this->getReference('B2bCustomer_'. LoadB2bCustomerEntitiesData::THIRD_ENTITY_NAME);

        $content = json_encode([
            'contactId' => $customer->getId(),
            'phone' =>'test1@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyCustomerId()
    {
        $content = json_encode([
            'phone' =>'test@test.test',
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testEmptyPhone()
    {
        $customer = $this->getReference('B2bCustomer_'. LoadB2bCustomerEntitiesData::THIRD_ENTITY_NAME);
        $content = json_encode([
            'contactId' => $customer->getId(),
            'primary' => true
        ]);

        $this->client->request('POST', $this->getUrl('oro_api_post_b2bcustomer_phone'), [], [], [], $content);
        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_BAD_REQUEST);
    }

    public function testDeletePhoneForbidden()
    {
        $customerPhone = $this
            ->getReference('B2bCustomerPhone_Several_'. LoadB2bCustomerPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $customerPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_b2bcustomer_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(
            '{"code":500,"message":"oro.b2bcustomer.phone.error.delete.more_one"}',
            $this->client->getResponse()->getContent()
        );
    }

    public function testDeletePhoneSuccess()
    {
        $customerPhone = $this->getReference('B2bCustomerPhone_Single_'. LoadB2bCustomerPhoneData::FIRST_ENTITY_NAME);
        $routeParams = [
            'id' => $customerPhone->getId()
        ];
        $this->client->request('DELETE', $this->getUrl('oro_api_delete_b2bcustomer_phone', $routeParams));

        $this->getJsonResponseContent($this->client->getResponse(), Codes::HTTP_OK);
        $this->assertEquals('{"id":""}', $this->client->getResponse()->getContent());
    }
}
