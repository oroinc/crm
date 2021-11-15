<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\SalesBundle\Provider\ProbabilityProvider;

class ProbabilityProviderTest extends \PHPUnit\Framework\TestCase
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

        $this->assertEquals($this->getDefaultProbabilities(), $provider->getAll());
    }

    private function getDefaultProbabilities(): array
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

    private function getProvider(): ProbabilityProvider
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($this->getDefaultProbabilities());

        return new ProbabilityProvider($configManager);
    }

    private function getOpportunityStatus(string $id): AbstractEnumValue
    {
        return new TestEnumValue($id, $id);
    }
}
