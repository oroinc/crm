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

    /**
     * @return array
     */
    protected function getDefaultProbabilities()
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
     * @param AbstractEnumValue[] $defaultStatuses
     *
     * @return OpportunityType
     */
    protected function getFormType(array $defaultStatuses = [])
    {
        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
        $configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($this->getDefaultProbabilities());

        $probabilityProvider = new ProbabilityProvider($configManager);

        $defaultStatuses = array_map(function ($id) {
            return $this->getOpportunityStatus($id);
        }, $defaultStatuses);

        $doctrineHelper = $this->getDoctrineHelperMock($defaultStatuses);
        /** @var EnumTranslationCache|\PHPUnit\Framework\MockObject\MockObject $cache */
        $cache = $this->createMock(EnumTranslationCache::class);
        $enumProvider = new EnumValueProvider($doctrineHelper, $cache);
        $helper = $this->getEnumTypeHelperMock();

        return new OpportunityType(
            $probabilityProvider,
            $enumProvider,
            $helper,
            new OpportunityRelationsBuilder()
        );
    }

    /**
     * @param AbstractEnumValue[] $defaultValues
     *
     * @return DoctrineHelper
     */
    protected function getDoctrineHelperMock(array $defaultValues)
    {
        $repo = $this->getMockBuilder(EnumValueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->any())
            ->method('getDefaultValues')
            ->willReturn($defaultValues);

        $doctrine = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine->expects($this->any())
            ->method('getEntityRepository')
            ->willReturn($repo);

        /** @var DoctrineHelper $doctrine */
        return $doctrine;
    }

    /**
     * @return EnumTypeHelper
     */
    protected function getEnumTypeHelperMock()
    {
        $helper = $this->getMockBuilder(EnumTypeHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper->expects($this->any())
            ->method('getEnumCode')
            ->willReturn('opportunity_status');

        /** @var EnumTypeHelper $helper */
        return $helper;
    }

    /**
     * @param mixed $data
     *
     * @return FormEvent
     */
    protected function getFormEvent($data = null)
    {
        $form = $this->createMock(FormInterface::class);

        return new FormEvent($form, $data);
    }

    /**
     * @param string $id
     *
     * @return AbstractEnumValue
     */
    protected function getOpportunityStatus($id)
    {
        return new TestEnumValue($id, $id);
    }

    /**
     * @param string|null $statusId
     * @param float|null $probability
     *
     * @return OpportunityStub
     */
    protected function getOpportunity($statusId = null, $probability = null)
    {
        $opportunity = new OpportunityStub();

        if ($statusId) {
            $opportunity->setStatus($this->getOpportunityStatus($statusId));
        }

        if ($probability) {
            $opportunity->setProbability($probability);
        }

        return $opportunity;
    }
}
