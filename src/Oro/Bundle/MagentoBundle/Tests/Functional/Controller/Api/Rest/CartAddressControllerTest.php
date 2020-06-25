<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CartAddressControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        $this->initClient(['debug' => false], $this->generateWsseAuthHeader());

        $this->loadFixtures(
            [
                'Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel'
            ]
        );
    }

    /**
     * @return array
     */
    public function testPostShipping()
    {
        $cartID  = $this->getCartId();
        $request = [
            'label'        => 'new shipping',
            'street'       => 'street',
            'city'         => 'new city',
            'postalCode'   => '10000',
            'country'      => 'US',
            'region'       => 'US-AL',
            'firstName'    => 'first',
            'lastName'     => 'last',
            'nameSuffix'   => 'suffix',
            'phone'        => '2352345234',
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_cart_address_shipping', ['cartId' => $cartID]),
            $request
        );

        $address = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $address);
        $this->assertNotEmpty($address['id']);

        return [
            'shippingAddressId' => $address['id'],
            'cartId'            => $cartID,
            'shippingRequest'   => $request
        ];
    }

    /**
     * @param array $param
     *
     * @return array
     *
     * @depends testPostShipping
     */
    public function testGetShippingAddress($param)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_cart_address_shipping',
                [
                    'cartId' => $param['cartId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($entity);
        $this->assertNotEmpty($entity['id']);
        $this->assertEquals($param['shippingAddressId'], $entity['id']);

        $param['request'] = $entity;

        return $param;
    }

    /**
     * @param $param
     *
     * @return array
     *
     * @depends testGetShippingAddress
     */
    public function testUpdateShipping($param)
    {
        $param['request']['firstName'] .= '_Updated';
        $param['request']['lastName'] .= '_Updated';

        $request = $param['request'];

        unset(
            $request['id'],
            $request['created'],
            $request['updated'],
            $request['originId'],
            $request['regionText'],
            $request['organization'],
            $request['channel']
        );

        $this->client->request(
            'PUT',
            $this->getUrl(
                'oro_api_put_cart_address_shipping',
                [
                    'cartId' => $param['cartId']
                ]
            ),
            $request
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_cart_address_shipping',
                [
                    'cartId' => $param['cartId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($entity['firstName'], $param['request']['firstName']);
        $this->assertEquals($entity['lastName'], $param['request']['lastName']);

        return $param;
    }

    /**
     * @param array $param
     *
     * @depends testUpdateShipping
     */
    public function testDeleteShipping(array $param)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl(
                'oro_api_delete_cart_address_shipping',
                [
                    'cartId' => $param['cartId']
                ]
            )
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_cart_address_shipping',
                [
                    'cartId' => $param['cartId']
                ]
            )
        );
        $this->getJsonResponseContent($this->client->getResponse(), 404);
    }

    /**
     * @return array
     */
    public function testPostBilling()
    {
        $cartID  = $this->getCartId();
        $request = [
            'label'        => 'new billing',
            'street'       => 'street',
            'city'         => 'new city',
            'postalCode'   => '10000',
            'country'      => 'US',
            'region'       => 'US-AL',
            'firstName'    => 'first',
            'lastName'     => 'last',
            'nameSuffix'   => 'suffix',
            'phone'        => '2352345234'
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_cart_address_billing', ['cartId' => $cartID]),
            $request
        );

        $address = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $address);
        $this->assertNotEmpty($address['id']);

        return [
            'billingAddressId' => $address['id'],
            'cartId'           => $cartID,
            'billingRequest'   => $request
        ];
    }

    /**
     * @param array $param
     *
     * @return array
     *
     * @depends testPostBilling
     */
    public function testGetBillingAddress($param)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_cart_address_billing',
                [
                    'cartId' => $param['cartId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($entity);
        $this->assertNotEmpty($entity['id']);
        $this->assertEquals($param['billingAddressId'], $entity['id']);

        $param['request'] = $entity;

        return $param;
    }

    /**
     * @param $param
     *
     * @return array
     *
     * @depends testGetBillingAddress
     */
    public function testUpdateBilling($param)
    {
        $param['request']['firstName'] .= '_Updated';
        $param['request']['lastName'] .= '_Updated';

        $request = $param['request'];

        unset(
            $request['id'],
            $request['created'],
            $request['updated'],
            $request['originId'],
            $request['regionText'],
            $request['organization'],
            $request['channel']
        );

        $this->client->request(
            'PUT',
            $this->getUrl(
                'oro_api_put_cart_address_billing',
                [
                    'cartId' => $param['cartId']
                ]
            ),
            $request
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_cart_address_billing',
                [
                    'cartId' => $param['cartId']
                ]
            )
        );

        $entity = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($entity['firstName'], $param['request']['firstName']);
        $this->assertEquals($entity['lastName'], $param['request']['lastName']);

        return $param;
    }

    /**
     * @param array $param
     *
     * @depends testUpdateBilling
     */
    public function testDeleteBilling(array $param)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl(
                'oro_api_delete_cart_address_billing',
                [
                    'cartId' => $param['cartId']
                ]
            )
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_cart_address_billing',
                [
                    'cartId' => $param['cartId']
                ]
            )
        );
        $this->getJsonResponseContent($this->client->getResponse(), 404);
    }

    /**
     * @return Cart
     */
    protected function getCartId()
    {
        return $this->getReference('cart')->getId();
    }
}
