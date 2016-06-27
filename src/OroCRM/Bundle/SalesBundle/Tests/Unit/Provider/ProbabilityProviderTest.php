<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;

use OroCRM\Bundle\SalesBundle\Provider\ProbabilityProvider;

class ProbabilityProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldReturnProbabilityForExistingStatus()
    {
        $provider = $this->getProvider();
        $status = $this->getOpportunityStatus('negotiation');

        $this->assertEquals(0.8, $provider->get($status));
    }

    public function testShouldReturnNullForUnknownStatus()
    {
        $provider = $this->getProvider();
        $status = $this->getOpportunityStatus('dummy');

        $this->assertNull($provider->get($status));
    }

    public function testShouldReturnProbabilityMap()
    {
        $provider = $this->getProvider();

        $this->assertEquals($this->getDefaultProbilities(), $provider->getAll());
    }

    /**
     * @return array
     */
    private function getDefaultProbilities()
    {
        return [
            'identification_alignment' => 0.3,
            'needs_analysis' => 0.2,
            'solution_development' => 0.5,
            'negotiation' => 0.8,
            'in_progress' => 0.1,
            'won' => 1.0,
            'lost' => 0.0,
        ];
    }

    /**
     * @return ProbabilityProvider
     */
    private function getProvider()
    {
        $configManager = $this->getConfigManagerMock();
        $provider = new ProbabilityProvider($configManager);

        return $provider;
    }

    /**
     * @return ConfigManager
     */
    private function getConfigManagerMock()
    {
        $configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($this->getDefaultProbilities());

        return $configManager;
    }

    /**
     * @param string $id
     *
     * @return AbstractEnumValue
     */
    private function getOpportunityStatus($id)
    {
        return new TestEnumValue($id, $id);
    }
}
