<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
abstract class AbstractController extends WebTestCase
{
    /** @var \Oro\Bundle\IntegrationBundle\Entity\Channel */
    protected static $channel;

    public function setUp()
    {
        $this->initClient(array('debug' => false), $this->generateBasicAuthHeader());

        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel']);
    }

    abstract protected function getMainEntityId();

    protected function postFixtureLoad()
    {
        self::$channel = $this->getContainer()
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
            $filters['gridParameters'][$gridChannel] = self::$channel->getId();
        }

        $this->client->requestGrid($filters['gridParameters'], $filters['gridFilters']);
        $response = $this->client->getResponse();
        $result   = $this->getJsonResponseContent($response, 200);

        foreach ($result['data'] as $row) {
            if ((isset($filters['gridParameters']['id'])) || ($filters['channelName'] === $row['channelName'])) {
                foreach ($filters['assert'] as $fieldName => $value) {
                    $this->assertEquals($value, $row[$fieldName]);
                }
                break;
            }
        }

        $this->assertCount((int)$filters['expectedResultCount'], $result['data']);
    }
}
