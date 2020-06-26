<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CartItemControllerTest extends WebTestCase
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
        $cartId = $this->getReference(LoadMagentoChannel::CART_ALIAS_REFERENCE_NAME)->getId();

        $request = [
            'sku'            => 'some sku',
            'name'           => 'some name',
            'qty'            => 10,
            'price'          => 100,
            'discountAmount' => 10,
            'taxPercent'     => 5,
            'weight'         => 1,
            'productId'      => 100500,
            'parentItemId'   => 100499,
            'freeShipping'   => 'nope',
            'giftMessage'    => 'some gift',
            'taxClassId'     => 'some tax',
            'description'    => '',
            'isVirtual'      => true,
            'customPrice'    => 100,
            'priceInclTax'   => 100,
            'rowTotal'       => 100,
            'taxAmount'      => 10,
            'productType'    => 'some type',
            'cart'           => $cartId
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_cart_item', ['cartId' => $cartId]),
            $request
        );

        $cartItem = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $cartItem);
        $this->assertNotEmpty($cartItem['id']);

        return ['cartItemId' => $cartItem['id'], 'cartId' => $cartId, 'request' => $request];
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
                'oro_api_get_cart_item',
                [
                    'cartId' => $param['cartId'],
                    'itemId' => $param['cartItemId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($entity);
        $this->assertNotEmpty($entity['cart']);
        $this->assertEquals($param['request']['cart'], $param['cartId']);
        $this->assertEquals($param['request']['name'], $entity['name']);
        $this->assertEquals($param['request']['sku'], $entity['sku']);
        $this->assertEquals($param['request']['productId'], $entity['productId']);

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
            $this->getUrl('oro_api_get_cart_items', ['cartId' => $param['cartId']])
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
                'oro_api_put_cart_item',
                [
                    'cartId' => $param['cartId'],
                    'itemId'  => $param['cartItemId']
                ]
            ),
            $param['request']
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_cart_item',
                [
                    'cartId' => $param['cartId'],
                    'itemId'  => $param['cartItemId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($entity['name'], $param['request']['name']);
        $this->assertEquals($entity['sku'], $param['request']['sku']);

        return ['cartId' => $entity['cart'], 'itemId' => $entity['id']];
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
                'oro_api_delete_cart_item',
                [
                    'cartId' => $param['cartId'],
                    'itemId'  => $param['itemId']
                ]
            )
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_cart_item',
                [
                    'cartId' => $param['cartId'],
                    'itemId'  => $param['itemId']
                ]
            )
        );
        $this->getJsonResponseContent($this->client->getResponse(), 404);
    }
}
