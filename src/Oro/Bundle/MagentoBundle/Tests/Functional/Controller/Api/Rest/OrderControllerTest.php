<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @dbIsolationPerTest
 */
class OrderControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        $this->initClient(['debug' => false], $this->generateWsseAuthHeader());

        $this->loadFixtures([LoadMagentoChannel::class]);
    }

    public function testCget()
    {
        $this->client->request('GET', $this->getUrl('oro_api_get_orders'));
        $orders = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertGreaterThan(0, count($orders));
    }

    public function testPost()
    {
        $user       = $this->getUser();
        $customerID = $this->getCustomer()->getId();

        $request = [
            'incrementId'         => mt_rand(0, 10000000),
            'originId'            => 42,
            'isVirtual'           => true,
            'isGuest'             => false,
            'giftMessage'         => 'some message',
            'remoteIp'            => '127.0.0.1',
            'storeName'           => 'store name',
            'totalPaidAmount'     => 100,
            'totalInvoicedAmount' => 10,
            'totalRefundedAmount' => 100,
            'totalCanceledAmount' => 100,
            'notes'               => 'some note',
            'feedback'            => 'nope',
            'customerEmail'       => 'test details',
            'currency'            => 'AU',
            'couponCode'         => 'some coupon code',
            'taxAmount'           => 5,
            'discountPercent'     => 10,
            'subtotalAmount'      => 10,
            'shippingAmount'      => 10,
            'status'              => 'status',
            'owner'               => $user->getId(),
            'store'               => $this->getStore()->getId(),
            'dataChannel'         => $this->getChannel()->getId(),
            'channel'             => $this->getChannel()->getDataSource()->getId(),
            'customer'            => $customerID,
            'addresses'           => [
                [
                    'label'        => 'new1',
                    'street'       => 'street',
                    'city'         => 'new city',
                    'postalCode'   => '10000',
                    'country'      => 'US',
                    'region'       => 'US-AL',
                    'firstName'    => 'first',
                    'lastName'     => 'last',
                    'nameSuffix'   => 'suffix',
                    'primary'      => true,
                    'types'        => [AddressType::TYPE_BILLING]
                ],
                [
                    'label'        => 'new1',
                    'street'       => 'street',
                    'city'         => 'new city',
                    'postalCode'   => '10000',
                    'country'      => 'US',
                    'region'       => 'US-AL',
                    'firstName'    => 'first',
                    'lastName'     => 'last',
                    'nameSuffix'   => 'suffix',
                    'primary'      => false,
                    'types'        => [AddressType::TYPE_SHIPPING]
                ]
            ],
            'items'               => [
                $this->getOrderItemForRequest()
            ]
        ];

        $this->client->request('POST', $this->getUrl('oro_api_post_order'), $request);

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $result);
        $this->assertNotEmpty($result['id']);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_order',
                ['id' => $result['id']]
            )
        );
        /** @var array $order */
        $order = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(1, $order['items']);
        $this->assertCount(2, $order['addresses']);
    }

    public function testPut()
    {
        $id = $this->getReference(LoadMagentoChannel::ORDER_ALIAS_REFERENCE_NAME)->getId();
        $order = [
            'giftMessage'     => 'some message updated',
            'totalPaidAmount' => 100
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('oro_api_put_order', ['id' => $id]),
            $order
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('oro_api_get_order', ['id' => $id]));

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(1, $result['items']);
        $this->assertCount(0, $result['addresses']);

        $this->assertEquals($order['giftMessage'], $result['giftMessage']);
        $this->assertEquals($order['totalPaidAmount'], $result['totalPaidAmount']);

        return $id;
    }

    public function testDelete()
    {
        $id = $this->getReference(LoadMagentoChannel::ORDER_ALIAS_REFERENCE_NAME)->getId();
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_order', ['id' => $id])
        );

        $result = $this->client->getResponse();

        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('oro_api_get_order', ['id' => $id]));

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

    /**
     * @return array
     */
    protected function getOrderItemForRequest()
    {
        return [
            'name'            => 'some name',
            'sku'             => 'some sku',
            'qty'             => 10,
            'cost'            => 10,
            'price'           => 10,
            'weight'          => 10,
            'taxPercent'      => 1,
            'taxAmount'       => 10,
            'discountPercent' => 10,
            'discountAmount'  => 10,
            'rowTotal'        => 10,
            'productType'     => 'some type',
            'productOptions'  => 'some options',
            'isVirtual'       => false,
            'originalPrice'   => 10
        ];
    }
}
