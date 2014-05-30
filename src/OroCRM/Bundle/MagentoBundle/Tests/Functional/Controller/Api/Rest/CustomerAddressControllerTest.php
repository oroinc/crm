<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class CustomerAddressControllerTest extends WebTestCase
{
    /** @var Channel */
    protected $channel;

    /** @var Customer */
    protected $customer;

    public function setUp()
    {
        $this->initClient(array('debug' => false), $this->generateWsseAuthHeader());

        $this->loadFixtures(
            array(
                'OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel',
            )
        );
    }

    protected function postFixtureLoad()
    {
        $this->channel = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroIntegrationBundle:Channel')
            ->findOneByName('Demo Web store');

        $this->customer = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMMagentoBundle:Customer')
            ->findOneByChannel($this->channel);
    }

    protected function getCustomerId()
    {
        return $this->customer->getid();
    }

    /**
     * @dataProvider cgetProvider
     */
    public function testCget($hasId, $status)
    {
        $id = 122334522;

        if ($hasId) {
            $id = $this->getCustomerId();
        }

        $this->client->request(
            'GET',
            $this->getUrl('get_customer_addresses', ['customerId' => $id])
        );
        $this->assertCount(1, self::getJsonResponseContent($this->client->getResponse(), $status));
    }

    public function cgetProvider()
    {
        return [
            'response with status 200' => [
                true,
                200,
            ],
            'response with status 404' => [
                false,
                404,
            ]
        ];
    }
}
