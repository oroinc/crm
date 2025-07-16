<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\SalesBundle\Provider\ProbabilityProvider;
use PHPUnit\Framework\TestCase;

class ProbabilityProviderTest extends TestCase
{
    public function testShouldReturnProbabilityForExistingStatus(): void
    {
        $provider = $this->getProvider();
        $status = $this->getOpportunityStatus('negotiation');

        $this->assertEquals(0.8, $provider->get($status));
    }

    public function testShouldReturnNullForUnknownStatus(): void
    {
        $provider = $this->getProvider();
        $status = $this->getOpportunityStatus('dummy');

        $this->assertNull($provider->get($status));
    }

    public function testShouldReturnProbabilityMap(): void
    {
        $provider = $this->getProvider();

        $this->assertEquals($this->getDefaultProbabilities(), $provider->getAll());
    }

    private function getDefaultProbabilities(): array
    {
        return [
            'test.identification_alignment' => 0.3,
            'test.needs_analysis' => 0.2,
            'test.solution_development' => 0.5,
            'test.negotiation' => 0.8,
            'test.in_progress' => 0.1,
            'test.won' => 1.0,
            'test.lost' => 0.0,
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

    private function getOpportunityStatus(string $id): EnumOptionInterface
    {
        return new TestEnumValue('test', 'Test', $id);
    }
}
