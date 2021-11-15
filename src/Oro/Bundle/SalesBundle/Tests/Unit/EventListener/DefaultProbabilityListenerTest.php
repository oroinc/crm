<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\SalesBundle\EventListener\DefaultProbabilityListener;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\OpportunityStub;
use Oro\Bundle\WorkflowBundle\Restriction\RestrictionManager;

class DefaultProbabilityListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider statusDataProvider
     */
    public function testShouldSetProbabilityOnUpdate(string $statusId, float $expectedProbability)
    {
        $opportunity = $this->getOpportunity($statusId, $this->getDefaultProbabilities()[$statusId]);
        $eventArguments = $this->getPreUpdateEventArguments($opportunity, [
            'status' => [
                $this->getOpportunityStatus($statusId),
                $this->getOpportunityStatus('won')
            ]
        ]);
        $listener = $this->getListener();
        $listener->preUpdate($opportunity, $eventArguments);

        $this->assertEquals($expectedProbability, $opportunity->getProbability());
    }

    public function testShouldNotOverwriteProbabilityOnUpdate()
    {
        $opportunity = $this->getOpportunity('negotiation', 0.25);
        $eventArguments = $this->getPreUpdateEventArguments($opportunity, [
            'probability' => [0.1, 0.25],
            'status' => [
                $this->getOpportunityStatus('negotiation'),
                $this->getOpportunityStatus('won')
            ]
        ]);
        $listener = $this->getListener();
        $listener->preUpdate($opportunity, $eventArguments);

        $this->assertEquals(0.25, $opportunity->getProbability());
        $this->assertEquals(0.25, $eventArguments->getNewValue('probability'));
    }

    public function testShouldNotSetProbabilityWithoutStatusChangeOnUpdate()
    {
        $opportunity = $this->getOpportunity('negotiation', 0.25);
        $eventArguments = $this->getPreUpdateEventArguments($opportunity, [
            'probability' => [0.1, 0.25]
        ]);
        $listener = $this->getListener();
        $listener->preUpdate($opportunity, $eventArguments);

        $this->assertEquals(0.25, $opportunity->getProbability());
        $this->assertEquals(0.25, $eventArguments->getNewValue('probability'));
    }

    public function testShouldNotModifyRestrictedFieldsOnUpdate()
    {
        $opportunity = $this->getOpportunity('solution_development', 0.25);
        $eventArguments = $this->getPreUpdateEventArguments($opportunity, [
            'status' => [
                $this->getOpportunityStatus('negotiation'),
                $this->getOpportunityStatus('won')
            ]
        ]);
        $listener = $this->getListener(true);
        $listener->preUpdate($opportunity, $eventArguments);

        $this->assertEquals(0.25, $opportunity->getProbability());
    }

    public function statusDataProvider(): array
    {
        return [
            [
                'statusId' => 'solution_development',
                'probability' => 0.5
            ],
            [
                'statusId' => 'won',
                'probability' => 1.0
            ],
            [
                'statusId' => 'lost',
                'probability' => 0.0
            ],
        ];
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

    private function getListener(bool $hasRestriction = false): DefaultProbabilityListener
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($this->getDefaultProbabilities());

        $restrictionManager = $this->createMock(RestrictionManager::class);
        $restrictionManager->expects($this->any())
            ->method('getEntityRestrictions')
            ->willReturn([['field' => 'probability']]);
        $restrictionManager->expects($this->any())
            ->method('hasEntityClassRestrictions')
            ->willReturn($hasRestriction);

        return new DefaultProbabilityListener($configManager, $restrictionManager);
    }

    private function getPreUpdateEventArguments(object $object, array $changeSet = []): PreUpdateEventArgs
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->createMock(UnitOfWork::class));
        $em->expects($this->any())
            ->method('getClassMetadata')
            ->willReturn($this->createMock(ClassMetadata::class));

        return new PreUpdateEventArgs($object, $em, $changeSet);
    }

    private function getOpportunity(string $statusId, float $probability = null): OpportunityStub
    {
        $opportunity = new OpportunityStub();
        $opportunity->setStatus($this->getOpportunityStatus($statusId));
        $opportunity->setProbability($probability);

        return $opportunity;
    }

    private function getOpportunityStatus(string $id): AbstractEnumValue
    {
        $enum = $this->createMock(AbstractEnumValue::class);
        $enum->expects($this->any())
            ->method('getId')
            ->willReturn($id);

        return $enum;
    }
}
