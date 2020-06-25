<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class OrderItemControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        $this->initClient(['debug' => false], $this->generateWsseAuthHeader());

        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    /**
     * @return array
     */
    public function testPost()
    {
        $orderId = $this->getReference(LoadMagentoChannel::ORDER_ALIAS_REFERENCE_NAME)->getId();

        $request = [
            'name'            => 'some name',
            'sku'             => 'some sku',
            'qty'             => 11,
            'cost'            => 12,
            'price'           => 13,
            'weight'          => 1,
            'taxPercent'      => 5,
            'taxAmount'       => 14,
            'discountPercent' => 5,
            'discountAmount'  => 15,
            'rowTotal'        => 16,
            'order'           => $orderId,
            'productType'     => 'some productType',
            'productOptions'  => 'some productOptions',
            'isVirtual'       => true,
            'originalPrice'   => 100,
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_order_item', ['orderId' => $orderId]),
            $request
        );

        $orderItem = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $orderItem);
        $this->assertNotEmpty($orderItem['id']);

        return ['orderItemId' => $orderItem['id'], 'orderId' => $orderId, 'request' => $request];
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
                'oro_api_get_order_item',
                [
                    'orderId' => $param['orderId'],
                    'itemId'  => $param['orderItemId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($entity);
        $this->assertNotEmpty($entity['order']);
        $this->assertEquals($param['request']['name'], $entity['name']);
        $this->assertEquals($param['request']['sku'], $entity['sku']);

        $param['request'] = $entity;

        return $param;
    }

    /**
     * @param array $param
     *
     * @return array
     *
     * @depends testPost
     */
    public function testCget($param)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_order_items', ['orderId' => $param['orderId']])
        );

        $response = self::getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertGreaterThan(1, count($response));
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
        $param['request']['name'] .= "_Updated";
        $param['request']['sku'] .= "_Updated";

        $this->client->request(
            'PUT',
            $this->getUrl(
                'oro_api_put_order_item',
                [
                    'orderId' => $param['orderId'],
                    'itemId'  => $param['orderItemId']
                ]
            ),
            $param['request']
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_order_item',
                [
                    'orderId' => $param['orderId'],
                    'itemId'  => $param['orderItemId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($entity['name'], $param['request']['name']);
        $this->assertEquals($entity['sku'], $param['request']['sku']);

        return ['orderId' => $entity['order'], 'itemId' => $entity['id']];
    }

    /**
     * @param $param
     *
     * @depends testUpdate
     */
    public function testDelete($param)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl(
                'oro_api_delete_order_item',
                [
                    'orderId' => $param['orderId'],
                    'itemId'  => $param['itemId']
                ]
            )
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_order_item',
                [
                    'orderId' => $param['orderId'],
                    'itemId'  => $param['itemId']
                ]
            )
        );
        $this->getJsonResponseContent($this->client->getResponse(), 404);
    }
}
