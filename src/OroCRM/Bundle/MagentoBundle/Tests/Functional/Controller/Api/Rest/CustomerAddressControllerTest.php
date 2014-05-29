<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

/**
 * @ outputBuffering enabled
 * @ dbIsolation
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

    public function testCget()
    {
        $this->client->request(
            'GET',
            $this->getUrl('get_customer_addresses', ['customerId' => $this->getCustomerId()])
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 200);
        $data  = json_decode($result->getContent(), 1);
        $this->assertGreaterThanOrEqual(count($data), 1);
    }
}
