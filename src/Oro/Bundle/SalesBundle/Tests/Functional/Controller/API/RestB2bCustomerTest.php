<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller\API;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RestB2bCustomerTest extends WebTestCase
{
    private array $testAddress = [
        'street'     => 'street',
        'city'       => 'city',
        'country'    => 'United States',
        'region'     => 'Florida',
        'postalCode' => '12345'
    ];

    /** @var Channel */
    private static $dataChannel;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadSalesBundleFixtures::class]);
    }

    protected function postFixtureLoad()
    {
        self::$dataChannel = $this->getReference('default_channel');
    }

    public function testCreateB2bCustomer(): array
    {
        $request = [
            'b2bcustomer' => [
                'name'                          => 'b2bcustomer_name_' . random_int(1, 500),
                'customer_association_account'  => $this->getReference('default_account')->getId(),
                'owner'                         => '1',
                'dataChannel'                   => self::$dataChannel->getId(),
                'shippingAddress'               => $this->testAddress
            ]
        ];

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_b2bcustomer'),
            $request
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 201);

        $request['id'] = $result['id'];

        return $request;
    }

    /**
     * @depends testCreateB2bCustomer
     */
    public function testGetB2bCustomer(array $request): array
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_b2bcustomer', ['id' => $request['id']])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['b2bcustomer']['name'], $result['name']);
        $this->assertEquals($request['b2bcustomer']['customer_association_account'], $result['account']);

        // assert addresses
        $this->assertEquals(
            array_intersect_key($request['b2bcustomer']['shippingAddress'], $this->testAddress),
            array_intersect_key($result['shippingAddress'], $this->testAddress)
        );
        $this->assertNull($result['billingAddress']);

        return $request;
    }

    /**
     * @depends testGetB2bCustomer
     */
    public function testUpdateB2bCustomer(array $request): array
    {
        $request['b2bcustomer']['name'] .= '_updated';

        $request['b2bcustomer']['shippingAddress']['street'] .= '_updated';

        $this->client->request(
            'PUT',
            $this->getUrl('oro_api_put_b2bcustomer', ['id' => $request['id']]),
            $request
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_b2bcustomer', ['id' => $request['id']])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['b2bcustomer']['name'], $result['name']);

        // assert addresses
        $this->assertEquals(
            $request['b2bcustomer']['shippingAddress']['street'],
            $result['shippingAddress']['street']
        );

        return $request;
    }

    /**
     * @depends testUpdateB2bCustomer
     */
    public function testGetB2bCustomers(array $request)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_b2bcustomers')
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertNotEmpty($result);

        $result = end($result);
        $this->assertEquals($request['id'], $result['id']);
        $this->assertEquals($request['b2bcustomer']['name'], $result['name']);
    }

    /**
     * @depends testUpdateB2bCustomer
     */
    public function testDeleteB2bCustomer(array $request)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_b2bcustomer', ['id' => $request['id']])
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_b2bcustomer', ['id' => $request['id']])
        );

        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }
}
