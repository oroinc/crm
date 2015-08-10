<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class MagentoCustomerControllerTest extends WebTestCase
{
    const CHANNEL_NAME = 'Demo Web store';
    const WEBSITE_CODE = 'web site code';
    const STORE_NAME = 'demo store';
    const GROUP_NAME = 'group';

    /** @var Channel */
    protected $channel;

    /** @var Customer */
    protected $customer;

    /** @var Website */
    protected $website;

    /** @var Store */
    protected $store;

    /** @var CustomerGroup */
    protected $customerGroup;

    public function setUp()
    {
        $this->initClient(['debug' => false], $this->generateWsseAuthHeader());

        $this->loadFixtures(
            [
                'OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel',
            ]
        );
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
     * Get loaded website
     *
     * @return Website
     */
    protected function getWebsite()
    {
        return $this->getStore()->getWebsite();
    }

    /**
     * Get loaded store
     *
     * @return Store
     */
    protected function getStore()
    {
        return $this->getReference('store');
    }

    /**
     * Get loaded Customer group
     *
     * @return CustomerGroup
     */
    protected function getCustomerGroup()
    {
        if (is_null($this->customerGroup)) {
            $this->customerGroup = self::getContainer()
                ->get('doctrine')
                ->getRepository('OroCRMMagentoBundle:CustomerGroup')
                ->findOneByName(self::GROUP_NAME);
        } else {
            $this->customerGroup = false;
        }

        return $this->customerGroup;
    }

    /**
     * @return array
     */
    public function testCreateCustomer()
    {
        $user = $this->getUser();

        $request = [
            'namePrefix'   => '',
            'firstName'    => 'Customer_fname_' . mt_rand(),
            'lastName'     => 'Customer_lname',
            'gender'       => 'male',
            'birthday'     => '1982-10-10',
            'email'        => 'test' . mt_rand() . '@gmail.com',
            'owner'        => $user->getId(),
            'originId'     => mt_rand(),
            'dataChannel'  => $this->getChannel()->getId(),
            'store'        => $this->getStore()->getId(),
            'website'      => $this->getWebsite()->getId(),
            'group'        => $this->getCustomerGroup()->getId(),
            'addresses'    => [
                [
                    'label'        => 'new1',
                    'street'       => 'street',
                    'city'         => 'new city',
                    'postalCode'   => '10000',
                    'country'      => 'US',
                    'region'       => 'US-AL',
                    'namePrefix'   => '',
                    'firstName'    => 'first',
                    'middleName'   => 'middle',
                    'lastName'     => 'last',
                    'nameSuffix'   => 'suffix',
                    'primary'      => true,
                    'types'        => ['billing']
                ]
            ]
        ];

        $this->client->request('POST', $this->getUrl('oro_api_post_magentocustomer'), $request);

        $customer = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $this->assertArrayHasKey('id', $customer);
        $this->assertNotEmpty($customer['id']);
        $request['id'] = $customer['id'];

        return $request;
    }

    /**
     * TODO: DateTimeZone should be removed in BAP-8710. Test should be passed.
     * @depends testCreateCustomer
     */
    public function testCget()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_magentocustomers', ['startUpdatedAt' => $date->format('Y-m-d')])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertGreaterThan(1, count($result));

        $date->modify('+ 1 day');
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_magentocustomers', ['endCreatedAt' => $date->format('Y-m-d')])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertGreaterThan(1, count($result));
    }

    /**
     * @param $request
     *
     * @depends testCreateCustomer
     *
     * @return array
     */
    public function testGetCustomer($request)
    {
        $this->client->request('GET', $this->getUrl('oro_api_get_magentocustomer', ['id' => $request['id']]));

        $selectedCustomer = $this->getJsonResponseContent($this->client->getResponse(), 200);

        // assert related entities
        foreach (['website', 'store', 'group', 'dataChannel'] as $key) {
            $this->assertEquals($request[$key], $selectedCustomer[$key]);
        }

        $this->assertNotEmpty($selectedCustomer['addresses']);

        $request['id'] = $selectedCustomer['id'];

        return $request;
    }

    /**
     * @param array $request
     *
     * @depends testGetCustomer
     */
    public function testUpdateCustomer($request)
    {
        $request['firstName'] .= '_Updated';
        $request['lastName']  .= '_Updated';

        $id = $request['id'];
        unset($request['id'], $request['organization']);

        $this->client->request(
            'PUT',
            $this->getUrl('oro_api_put_magentocustomer', ['id' => $id]),
            $request
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request('GET', $this->getUrl('oro_api_get_magentocustomer', ['id' => $id]));
        $customer = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['firstName'], $customer['firstName'], 'Customer was not updated');

        // assert related entities
        foreach (['website', 'store', 'group', 'dataChannel'] as $key) {
            $this->assertEquals($request[$key], $customer[$key]);
        }

        return $id;
    }

    /**
     * @param $id
     *
     * @depends testUpdateCustomer
     */
    public function testDeleteCustomer($id)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_magentocustomer', ['id' => $id])
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request('GET', $this->getUrl('oro_api_get_magentocustomer', ['id' => $id]));
        $this->getJsonResponseContent($this->client->getResponse(), 404);
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
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
