<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ChannelGridTest extends WebTestCase
{
    const CHANNEL_NAME = 'some name';
    const GRID_NAME    = 'orocrm-channels-grid';

    /** @var Channel */
    public static $channel;

    public function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );
        $this->loadFixtures(['OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannel']);
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

            $filters['gridParameters'][$gridId] = $this->getReference('default_channel')->getId();
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

        $this->assertCount((int) $filters['expectedResultCount'], $result['data']);
    }

    public function gridProvider()
    {
        return [
            'Channel grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => self::GRID_NAME
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'name' => self::CHANNEL_NAME,
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Channel grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => self::GRID_NAME
                    ],
                    'gridFilters'         => [
                        self::GRID_NAME . '[_filter][name][value]' => 'Not found',
                    ],
                    'assert'              => [
                        'name' => self::CHANNEL_NAME,
                    ],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
