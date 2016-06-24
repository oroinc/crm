<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;

use OroCRM\Bundle\SalesBundle\Form\Type\OpportunityType;
use OroCRM\Bundle\SalesBundle\Provider\ProbabilityProvider;
use OroCRM\Bundle\SalesBundle\Tests\Unit\Stub\Opportunity;

class OpportunityTypeTest extends \PHPUnit_Framework_TestCase
{

    public function testShoultNotOverwriteProbability()
    {
        $opportunity = $this->getOpportunity('negotiation', 0.7);
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType(['in_progress']);

        $type->onPreSetData($event);
        $this->assertEquals(0.7, $event->getData()->getProbability());
        $this->assertEquals(0.7, $opportunity->getProbability());
    }

    public function testShoultSetProbabilityBasedOnStatus()
    {
        $opportunity = $this->getOpportunity('negotiation');
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType(['in_progress']);

        $type->onPreSetData($event);
        $this->assertEquals(0.8, $event->getData()->getProbability());
    }

    public function testShoultSetProbabilityBasedOnDefaultStatus()
    {
        $opportunity = $this->getOpportunity();
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType(['in_progress']);

        $type->onPreSetData($event);
        $this->assertEquals(0.1, $event->getData()->getProbability());
    }

    public function testShoultNotChangeProbabilityWithoutDefaultStatus()
    {
        $opportunity = $this->getOpportunity(null, 0.7);
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType();

        $type->onPreSetData($event);
        $this->assertEquals(0.7, $event->getData()->getProbability());
        $this->assertEquals(0.7, $opportunity->getProbability());
    }

    public function testShoultNotChangeProbabilityWithUnknownStatus()
    {
        $opportunity = $this->getOpportunity('dummy', 0.7);
        $event = $this->getFormEvent($opportunity);
        $type = $this->getFormType(['in_progress']);

        $type->onPreSetData($event);
        $this->assertEquals(0.7, $event->getData()->getProbability());
        $this->assertEquals(0.7, $opportunity->getProbability());
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
     * @param AbstractEnumValue[] $defaultStatuses
     *
     * @return OpportunityStatusSelectType
     */
    private function getFormType(array $defaultStatuses = [])
    {
        $configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($this->getDefaultProbilities());

        $probabilityProvider = new ProbabilityProvider($configManager);

        $defaultStatuses = array_map(function ($id) {
            return $this->getOpportunityStatus($id);
        }, $defaultStatuses);

        $doctrineHelper = $this->getDoctrineHelperMock($defaultStatuses);
        $enumProvider = new EnumValueProvider($doctrineHelper);
        $helper = $this->getEnumTypeHelperMock();

        $type = new OpportunityType($probabilityProvider, $enumProvider, $helper);

        return $type;
    }

    /**
     * @param AbstractEnumValue[] $defaultValues
     *
     * @return DoctrineHelper
     */
    private function getDoctrineHelperMock(array $defaultValues)
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

        return $doctrine;
    }

    /**
     * @return EnumTypeHelper
     */
    private function getEnumTypeHelperMock()
    {
        $helper = $this->getMockBuilder(EnumTypeHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper->expects($this->any())
            ->method('getEnumCode')
            ->willReturn('opportunity_status');

        return $helper;
    }

    /**
     * @param mixed $data
     *
     * @return FormEvent
     */
    private function getFormEvent($data = null)
    {
        $form = $this->getMock(FormInterface::class);

        return new FormEvent($form, $data);
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

    /**
     * @param string|null $statusId
     * @param float|null $probability
     *
     * @return Opportunity
     */
    private function getOpportunity($statusId = null, $probability = null)
    {
        $opportunity = new Opportunity();

        if ($statusId) {
            $opportunity->setStatus($this->getOpportunityStatus($statusId));
        }

        if ($probability) {
            $opportunity->setProbability($probability);
        }

        return $opportunity;
    }
}
