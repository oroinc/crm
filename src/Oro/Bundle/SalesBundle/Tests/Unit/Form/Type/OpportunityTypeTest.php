<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Cache\EnumTranslationCache;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use Oro\Bundle\SalesBundle\Form\Type\OpportunityType;
use Oro\Bundle\SalesBundle\Provider\ProbabilityProvider;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\OpportunityStub;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class OpportunityTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldNotOverwriteProbability()
    {
        $opportunity = $this->getOpportunity('negotiation', 0.7);
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType(['in_progress']);

        $type->onFormPreSetData($event);
        $this->assertEquals(0.7, $event->getData()->getProbability());
        $this->assertEquals(0.7, $opportunity->getProbability());
    }

    public function testShouldSetProbabilityBasedOnStatus()
    {
        $opportunity = $this->getOpportunity('negotiation');
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType(['in_progress']);

        $type->onFormPreSetData($event);
        $this->assertEquals(0.8, $event->getData()->getProbability());
    }

    public function testShouldSetProbabilityBasedOnDefaultStatus()
    {
        $opportunity = $this->getOpportunity();
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType(['in_progress']);

        $type->onFormPreSetData($event);
        $this->assertEquals(0.1, $event->getData()->getProbability());
    }

    public function testShouldNotChangeProbabilityWithoutDefaultStatus()
    {
        $opportunity = $this->getOpportunity(null, 0.7);
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType();

        $type->onFormPreSetData($event);
        $this->assertEquals(0.7, $event->getData()->getProbability());
        $this->assertEquals(0.7, $opportunity->getProbability());
    }

    public function testShouldNotChangeProbabilityWithUnknownStatus()
    {
        $opportunity = $this->getOpportunity('dummy', 0.7);
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType(['in_progress']);

        $type->onFormPreSetData($event);
        $this->assertEquals(0.7, $event->getData()->getProbability());
        $this->assertEquals(0.7, $opportunity->getProbability());
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

    private function getFormType(array $defaultStatuses = []): OpportunityType
    {
        $configManager = $this->createMock(ConfigManager::class);

        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($this->getDefaultProbabilities());

        $probabilityProvider = new ProbabilityProvider($configManager);

        $defaultStatuses = array_map(function ($id) {
            return $this->getOpportunityStatus($id);
        }, $defaultStatuses);

        $repo = $this->createMock(EnumValueRepository::class);
        $repo->expects($this->any())
            ->method('getDefaultValues')
            ->willReturn($defaultStatuses);

        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->willReturn($repo);

        $enumTypeHelper = $this->createMock(EnumTypeHelper::class);
        $enumTypeHelper->expects($this->any())
            ->method('getEnumCode')
            ->willReturn('opportunity_status');

        return new OpportunityType(
            $probabilityProvider,
            new EnumValueProvider($doctrineHelper, $this->createMock(EnumTranslationCache::class)),
            $enumTypeHelper,
            new OpportunityRelationsBuilder()
        );
    }

    private function getFormEvent(OpportunityStub $opportunity): FormEvent
    {
        return new FormEvent($this->createMock(FormInterface::class), $opportunity);
    }

    private function getOpportunityStatus(string $id): AbstractEnumValue
    {
        return new TestEnumValue($id, $id);
    }

    private function getOpportunity(string $statusId = null, float $probability = null): OpportunityStub
    {
        $opportunity = new OpportunityStub();
        if (null !== $statusId) {
            $opportunity->setStatus($this->getOpportunityStatus($statusId));
        }
        if (null !== $probability) {
            $opportunity->setProbability($probability);
        }

        return $opportunity;
    }
}
