<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
abstract class AbstractController extends WebTestCase
{
    /** @var Integration */
    protected static $integration;

    public function setUp()
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());

        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel']);
    }

    abstract protected function getMainEntityId();

    protected function postFixtureLoad()
    {
        self::$integration = $this->getReference('integration');
    }

    /**
     * @dataProvider gridProvider
     *
     * @param array $requestData
     */
    public function testGrid($requestData)
    {
        $gridName = $requestData['gridParameters']['gridName'];

        $expectedResultCount   = (int)$requestData['expectedResultCount'];
        $shouldPassIdentifier  = isset($requestData['gridParameters']['id']);
        $shouldPassIntegration = isset($requestData['gridParameters']['channel']);
        $shouldAssertData      = $expectedResultCount === 1;

        if ($shouldPassIdentifier) {
            $paramName = $gridName . '[' . $requestData['gridParameters']['id'] . ']';
            $requestData['gridParameters'][$paramName] = $this->getMainEntityId();
        }

        if ($shouldPassIntegration) {
            $paramName = $gridName . '[' . $requestData['gridParameters']['channel'] . ']';
            $requestData['gridParameters'][$paramName] = self::$integration->getId();
        }

        $this->client->requestGrid($requestData['gridParameters'], $requestData['gridFilters']);
        $response = $this->client->getResponse();
        $result   = $this->getJsonResponseContent($response, 200);

        foreach ($result['data'] as $row) {
            if ($shouldAssertData) {
                foreach ($requestData['assert'] as $fieldName => $value) {
                    $this->assertEquals($value, $row[$fieldName]);
                }
                break;
            }
        }

        $this->assertCount($expectedResultCount, $result['data']);
    }
}
