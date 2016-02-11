<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class OrderControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(['debug' => false], $this->generateWsseAuthHeader());

        $this->loadFixtures(
            [
                'OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel'
            ]
        );
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

        return $order;
    }

    /**
     * @param array $order
     *
     * @return int $id
     *
     * @depends testPost
     */
    public function testPut($order)
    {
        $order['giftMessage']     .= '_Updated';
        $order['totalPaidAmount'] += 100;

        $id = $order['id'];
        unset(
            $order['id'],
            $order['items'],
            $order['addresses'],
            $order['createdAt'],
            $order['updatedAt'],
            $order['importedAt'],
            $order['syncedAt'],
            $order['firstName'],
            $order['lastName'],
            $order['cart'],
            $order['organization']
        );

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
        $this->assertCount(2, $result['addresses']);

        $this->assertEquals($order['giftMessage'], $result['giftMessage'], 'Customer was not updated');
        $this->assertEquals($order['totalPaidAmount'], $result['totalPaidAmount'], 'Customer was not updated');

        return $id;
    }

    /**
     * @param int $id
     *
     * @depends testPut
     */
    public function testDelete($id)
    {
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
        return $this->getEntityManager()->getRepository('OroUserBundle:User')->findOneByUsername(self::USER_NAME);
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
