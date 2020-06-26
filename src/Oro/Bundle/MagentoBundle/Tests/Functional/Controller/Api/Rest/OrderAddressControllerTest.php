<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class OrderAddressControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        $this->initClient(['debug' => false], $this->generateWsseAuthHeader());

        $this->loadFixtures(
            array(
                'Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel'
            )
        );
    }

    /**
     * @return array
     */
    public function testPost()
    {
        $orderID = $this->getOrderId();

        $request = [
            'label'        => 'new1',
            'street'       => 'street',
            'city'         => 'new city',
            'postalCode'   => '10000',
            'country'      => 'US',
            'region'       => 'US-AL',
            'firstName'    => 'first',
            'lastName'     => 'last',
            'nameSuffix'   => 'suffix',
            'phone'        => '2352345234',
            'primary'      => true,
            'types'        => [AddressType::TYPE_BILLING],
            'owner'        => $orderID
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_order_address', ['orderId' => $orderID]),
            $request
        );

        $address = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $address);
        $this->assertNotEmpty($address['id']);

        return ['addressId' => $address['id'], 'orderId' => $orderID, 'request' => $request];
    }

    /**
     * @depends testPost
     */
    public function testCget()
    {
        $id = $this->getOrderId();

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_order_addresses', ['orderId' => $id])
        );

        $response = self::getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertGreaterThan(0, count($response));
    }

    /**
     * @param array $param
     *
     * @return array
     *
     * @depends testPost
     */
    public function testGet($param)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_order_address',
                [
                    'addressId' => $param['addressId'],
                    'orderId'   => $param['orderId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($entity);
        $this->assertNotEmpty($entity['types']);
        $this->assertEquals($param['request']['firstName'], $entity['firstName']);
        $this->assertEquals($param['request']['lastName'], $entity['lastName']);

        $param['request'] = $entity;

        return $param;
    }

    /**
     * @param $param
     *
     * @return array
     *
     * @depends testPost
     */
    public function testUpdate($param)
    {
        $param['request']['firstName'] .= '_Updated';
        $param['request']['lastName']  .= '_Updated';

        unset($param['request']['organizaton']);

        $this->client->request(
            'PUT',
            $this->getUrl(
                'oro_api_put_order_address',
                [
                    'addressId' => $param['addressId'],
                    'orderId'   => $param['orderId']
                ]
            ),
            $param['request']
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_order_address',
                [
                    'addressId' => $param['addressId'],
                    'orderId'   => $param['orderId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($entity['firstName'], $param['request']['firstName']);
        $this->assertEquals($entity['lastName'], $param['request']['lastName']);

        return $entity['id'];
    }

    /**
     * @param $param
     *
     * @depends testPost
     */
    public function testDelete($param)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl(
                'oro_api_delete_order_address',
                [
                    'addressId'  => $param['addressId'],
                    'orderId' => $param['orderId']
                ]
            )
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_order_address',
                [
                    'addressId' => $param['addressId'],
                    'orderId'   => $param['orderId']
                ]
            )
        );
        $this->getJsonResponseContent($this->client->getResponse(), 404);
    }

    /**
     * @return Order
     */
    protected function getOrderId()
    {
        return $this->getReference('order')->getId();
    }
}
