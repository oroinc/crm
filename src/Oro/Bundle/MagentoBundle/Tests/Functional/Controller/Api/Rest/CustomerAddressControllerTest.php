<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;

class CustomerAddressControllerTest extends WebTestCase
{
    const SOME_CUSTOMER_ID = 24234;

    /** @var Channel */
    protected $channel;

    /** @var Customer */
    protected $customer;

    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        $this->initClient(['debug' => false], static::generateWsseAuthHeader());

        $this->loadFixtures(
            [
                'Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel'
            ]
        );
    }

    /**
     * @dataProvider cgetProvider
     */
    public function testCget($hasCustomer, $expectedStatus)
    {
        $id = self::SOME_CUSTOMER_ID;

        if ($hasCustomer) {
            $id = $this->getCustomerId();
        }

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_customer_addresses', ['customerId' => $id])
        );
        $response = static::getJsonResponseContent($this->client->getResponse(), $expectedStatus);

        static::assertCount((int)$hasCustomer, $response);
    }

    public function cgetProvider()
    {
        return [
            'response with status 200' => [
                true,
                200
            ],
            'response with status 404' => [
                false,
                404
            ]
        ];
    }

    /**
     * @return array
     */
    public function testCreate()
    {
        $customerID = $this->getCustomer()->getId();

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
            'owner'        => $customerID
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_customer_address', ['customerId' => $customerID]),
            $request
        );

        $address = static::getJsonResponseContent($this->client->getResponse(), 201);

        static::assertArrayHasKey('id', $address);
        static::assertNotEmpty($address['id']);

        return ['addressId' => $address['id'], 'customerId' => $customerID, 'request' => $request];
    }

    /**
     * @param array $param
     *
     * @return array
     *
     * @depends testCreate
     */
    public function testGet($param)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_get_customer_address',
                [
                    'addressId'  => $param['addressId'],
                    'customerId' => $param['customerId']
                ]
            )
        );

        $entity = static::getJsonResponseContent($this->client->getResponse(), 200);

        static::assertNotEmpty($entity);
        static::assertNotEmpty($entity['types']);
        static::assertEquals($param['request']['firstName'], $entity['firstName']);
        static::assertEquals($param['request']['lastName'], $entity['lastName']);

        $param['request'] = $entity;

        return $param;
    }

    /**
     * @param $param
     *
     * @return array
     *
     * @depends testCreate
     */
    public function testUpdate($param)
    {
        $param['request']['firstName'] .= '_Updated';
        $param['request']['lastName']  .= '_Updated';

        $this->client->request(
            'PUT',
            $this->getUrl(
                'oro_api_put_customer_address',
                [
                    'addressId'  => $param['addressId'],
                    'customerId' => $param['customerId']
                ]
            ),
            $param['request']
        );

        $result = $this->client->getResponse();
        static::assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_put_customer_address',
                [
                    'addressId'  => $param['addressId'],
                    'customerId' => $param['customerId']
                ]
            )
        );

        $entity = static::getJsonResponseContent($this->client->getResponse(), 200);

        static::assertEquals($entity['firstName'], $param['request']['firstName']);
        static::assertEquals($entity['lastName'], $param['request']['lastName']);

        $param['request'] = $entity;

        return $param;
    }

    /**
     * @param $param
     *
     * @depends testCreate
     */
    public function testDelete($param)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl(
                'oro_api_put_customer_address',
                [
                    'addressId'  => $param['addressId'],
                    'customerId' => $param['customerId']
                ]
            )
        );

        $result = $this->client->getResponse();
        static::assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_api_put_customer_address',
                [
                    'addressId'  => $param['addressId'],
                    'customerId' => $param['customerId']
                ]
            )
        );
        static::getJsonResponseContent($this->client->getResponse(), 404);
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->getEntityManager()->getRepository('OroUserBundle:User')->findOneByUsername(self::USER_NAME);
    }

    /**
     * @return ObjectManager
     */
    protected function getEntityManager()
    {
        return static::getContainer()->get('doctrine')->getManager();
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
     * @return int
     */
    protected function getCustomerId()
    {
        return $this->getCustomer()->getid();
    }
}
