<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

abstract class AbstractController extends WebTestCase
{
    /** @var Integration */
    protected static $integration;

    /** @var bool */
    protected $isRealGridRequest = false;

    protected function setUp()
    {
        $this->initClient(['debug' => false], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->loadFixtures(['OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel']);
    }

    /**
     * @afterClass
     */
    public function clear()
    {
        self::$integration = null;
        gc_collect_cycles();
    }

    /**
     * @return int
     */
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
        $shouldPassIdentifier  = array_key_exists('id', $requestData['gridParameters']);
        $shouldPassIntegration = array_key_exists('channel', $requestData['gridParameters']);
        $shouldPassRemoved     = array_key_exists('is_removed', $requestData['gridParameters']);
        $shouldAssertData      = $expectedResultCount === 1;

        if ($shouldPassIdentifier) {
            $paramName = $gridName . '[' . $requestData['gridParameters']['id'] . ']';
            $requestData['gridParameters'][$paramName] = $this->getMainEntityId();
        }

        if ($shouldPassIntegration) {
            $paramName = $gridName . '[' . $requestData['gridParameters']['channel'] . ']';
            $requestData['gridParameters'][$paramName] = self::$integration->getId();
        }

        if ($shouldPassRemoved) {
            $paramName = $gridName . '[' . $requestData['gridParameters']['is_removed'] . ']';
            $requestData['gridParameters'][$paramName] =
                $requestData['gridFilters']['magento-cart-grid[_filter][is_removed][value]'];
        }

        $response = $this->client->requestGrid(
            $requestData['gridParameters'],
            $requestData['gridFilters'],
            $this->isRealGridRequest
        );
        $result   = $this->getJsonResponseContent($response, 200);

        foreach ($result['data'] as $row) {
            if ($shouldAssertData) {
                foreach ($requestData['assert'] as $fieldName => $value) {
                    if (is_string($row[$fieldName])) {
                        $this->assertContains($value, $row[$fieldName], sprintf('Incorrect value for %s', $fieldName));
                    } else {
                        $this->assertEquals($value, $row[$fieldName], sprintf('Incorrect value for %s', $fieldName));
                    }
                }
                break;
            }
        }

        $this->assertCount($expectedResultCount, $result['data']);
    }
}
