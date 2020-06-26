<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @dbIsolationPerTest
 */
class CartControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        $this->initClient(['debug' => false], $this->generateWsseAuthHeader());

        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    public function testCget()
    {
        $this->client->request('GET', $this->getUrl('oro_api_get_carts'));
        $orders = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertGreaterThan(0, count($orders));
    }

    public function testPost()
    {
        $user = $this->getUser();

        $request = [
            'subTotal'          => 100,
            'grandTotal'        => 100,
            'taxAmount'         => 10,
            'cartItems'         => [
                [
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
                    'productType'    => 'some type'
                ]
            ],
            'customer'          => $this->getCustomer()->getId(),
            'store'             => $this->getStore()->getId(),
            'itemsQty'          => 100,
            'baseCurrencyCode'  => 'some text',
            'storeCurrencyCode' => 'some text 2',
            'quoteCurrencyCode' => 'some text 3',
            'storeToBaseRate'   => 10,
            'storeToQuoteRate'  => 10,
            'email'             => 'test@test.com',
            'giftMessage'       => 'some message',
            'isGuest'           => true,
            'shippingAddress'   => [
                'label'        => 'new1',
                'street'       => 'street',
                'city'         => 'new city',
                'postalCode'   => '10000',
                'country'      => 'US',
                'region'       => 'US-AL',
                'firstName'    => 'first',
                'lastName'     => 'last',
                'nameSuffix'   => 'suffix',
                'phone'        => '123123123'
            ],
            'billingAddress'    => [
                'label'        => 'new2',
                'street'       => 'street',
                'city'         => 'new city',
                'postalCode'   => '10000',
                'country'      => 'US',
                'region'       => 'US-AL',
                'firstName'    => 'first',
                'lastName'     => 'last',
                'nameSuffix'   => 'suffix',
                'phone'        => '123123123'
            ],
            'paymentDetails'    => 'some details',
            'status'            => 'open',
            'notes'             => 'some text 4',
            'statusMessage'     => 'some text 5',
            'owner'             => $user->getId(),
            'dataChannel'       => $this->getChannel()->getId(),
            'channel'           => $this->getChannel()->getDataSource()->getId()
        ];

        $this->client->request('POST', $this->getUrl('oro_api_post_cart'), $request);

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $result);
        $this->assertNotEmpty($result['id']);

        $this->client->request('GET', $this->getUrl('oro_api_get_cart', ['id' => $result['id']]));

        /** @var Cart $cart */
        $cart = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(1, $cart['cartItems']);
        $this->assertNotEmpty($cart['billingAddress']);
        $this->assertIsArray($cart['billingAddress']);
        $this->assertNotEmpty($cart['shippingAddress']);
        $this->assertIsArray($cart['shippingAddress']);
        $this->assertEquals(1, $cart['itemsCount']);
    }

    public function testPut()
    {
        $id = $this->getReference(LoadMagentoChannel::CART_ALIAS_REFERENCE_NAME)->getId();
        $cart = [
            'giftMessage'      => 'some message updated',
            'baseCurrencyCode' => 'some text updated'
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('oro_api_put_cart', ['id' => $id]),
            $cart
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('oro_api_get_cart', ['id' => $id]));

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(1, $result['cartItems']);
        $this->assertNotEmpty($result['billingAddress']);
        $this->assertIsArray($result['billingAddress']);
        $this->assertNotEmpty($result['shippingAddress']);
        $this->assertIsArray($result['shippingAddress']);
        $this->assertEquals(1, $result['itemsCount']);

        $this->assertEquals($cart['giftMessage'], $result['giftMessage']);
        $this->assertEquals($cart['baseCurrencyCode'], $result['baseCurrencyCode']);

        return $id;
    }

    public function testDelete()
    {
        $id = $this->getReference(LoadMagentoChannel::CART_ALIAS_REFERENCE_NAME)->getId();
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_cart', ['id' => $id])
        );

        $result = $this->client->getResponse();

        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('oro_api_get_cart', ['id' => $id]));

        $this->getJsonResponseContent($this->client->getResponse(), 404);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->getEntityManager()->getRepository(User::class)->findOneByUsername(self::USER_NAME);
    }

    /**
     * Get loaded channel
     *
     * @return Channel
     */
    protected function getChannel()
    {
        return $this->getReference('default_channel');
    }

    /**
     * @return Customer
     */
    protected function getCustomer()
    {
        return $this->getReference('customer');
    }

    /**
     * return Store
     */
    protected function getStore()
    {
        return $this->getReference('store');
    }
}
