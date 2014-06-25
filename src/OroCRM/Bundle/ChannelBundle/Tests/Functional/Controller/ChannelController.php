<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ChannelController extends WebTestCase
{
    const CHANNEL_NAME = 'some name';

    /** @var Channel */
    public static $channel;

    public function setUp()
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());
        $this->loadFixtures(['OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannel']);
    }

    protected function postFixtureLoad()
    {
        self::$channel = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMChannelBundle:Channel')
            ->findOneByName(self::CHANNEL_NAME);
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

        $this->client->requestGrid($filters['gridParameters'], $filters['gridFilters']);
        $response = $this->client->getResponse();
        $result   = $this->getJsonResponseContent($response, 200);

        foreach ($result['data'] as $row) {
            if ((isset($filters['gridParameters']['id']))) {
                foreach ($filters['assert'] as $fieldName => $value) {
                    $this->assertEquals($value, $row[$fieldName]);
                }
                break;
            }
        }

        $this->assertCount((int)$filters['expectedResultCount'], $result['data']);
    }

    public function gridProvider()
    {
        return [
            'Channel grid' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'channels-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name'  => self::CHANNEL_NAME,
                    ],
                    'expectedResultCount' => 1
                ],
            ],
        ];
    }

    protected function getMainEntityId()
    {
        return self::$channel->getid();
    }
}
