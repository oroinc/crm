<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class AbstractController extends WebTestCase
{
    /** @var \Oro\Bundle\IntegrationBundle\Entity\Channel */
    protected $channel;

    public function setUp()
    {
        $this->initClient(array('debug' => false), $this->generateBasicAuthHeader());

        $this->loadFixtures(
            array(
                'OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel',
            )
        );
    }

    abstract protected function getMainEntityId();

    protected function postFixtureLoad()
    {
        $this->channel = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroIntegrationBundle:Channel')
            ->findOneByName('Demo Web store');
    }

    /**
     * @dataProvider gridProvider
     *
     * @param array $filters
     */
    public function testGrid($filters)
    {
        if (isset($filters['gridParameters']['id'])) {
            $gridId = $filters['gridParameters']['gridName'] . '[' . $filters['gridParameters']['id'] . ']';
            $filters['gridParameters'][$gridId] = $this->getMainEntityId();
        }

        if (isset($filters['gridParameters']['channel'])) {
            $gridChannel = $filters['gridParameters']['gridName'] . '[' . $filters['gridParameters']['channel'] . ']';
            $filters['gridParameters'][$gridChannel] = $this->getMainEntityId();
        }

        $this->client->requestGrid($filters['gridParameters'], $filters['gridFilters']);
        $result = $this->client->getResponse();
        $this->assertTrue($result->isSuccessful());
        $this->assertTrue($result->isOk());
        $data  = json_decode($result->getContent(), 1);
        $count = 0;

        foreach ($data['data'] as $grid) {
            if (
                (isset($filters['gridParameters']['id']))
                ||
                ($filters['channelName'] === $grid['channelName'])
            ) {
                foreach ($filters['verifying'] as $fieldName => $value) {
                    ++$count;
                    $this->assertEquals($value, $grid[$fieldName]);
                }
                break;
            }
        }

        if ($filters['isResult']) {
            $this->assertGreaterThanOrEqual(1, $count);
        } else {
            $this->assertEquals($count, 0);
        }
    }
}
