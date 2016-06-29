<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\WorkflowBundle\Restriction\RestrictionManager;

use OroCRM\Bundle\SalesBundle\Tests\Unit\Stub\Opportunity;
use OroCRM\Bundle\SalesBundle\EventListener\DefaultProbabilityListener;

class DefaultProbabilityListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldApplyOnlyToOpportunityOnPersist()
    {
        $object = new \stdClass();
        $object->probability = 0.7;

        $eventArguments = $this->getPrePersistEventArguments($object);
        $listener = $this->getListener();
        $listener->prePersist($eventArguments);

        $this->assertEquals(0.7, $object->probability);
    }

    public function testShouldApplyOnlyToOpportunityOnUpdate()
    {
        $object = new \stdClass();
        $object->probability = 0.7;

        $eventArguments = $this->getPreUpdateEventArguments($object);
        $listener = $this->getListener();
        $listener->preUpdate($eventArguments);

        $this->assertEquals(0.7, $object->probability);
    }

    /**
     * @dataProvider statusDataProvider
     *
     * @param string $statusId
     * @param float $expectedProbability
     */
    public function testShouldSetProbabilityOnPersist($statusId, $expectedProbability)
    {
        $opportunity = $this->getOpportunity($statusId);
        $eventArguments = $this->getPrePersistEventArguments($opportunity);
        $listener = $this->getListener();
        $listener->prePersist($eventArguments);

        $this->assertEquals($expectedProbability, $opportunity->getProbability());
    }

    /**
     * @dataProvider statusDataProvider
     *
     * @param string $statusId
     * @param float $expectedProbability
     */
    public function testShouldSetProbabilityOnUpdate($statusId, $expectedProbability)
    {
        $opportunity = $this->getOpportunity($statusId);
        $eventArguments = $this->getPreUpdateEventArguments($opportunity, [
            'status' => [
                $this->getOpportunityStatus('negotiation'),
                $this->getOpportunityStatus('won')
            ]
        ]);
        $listener = $this->getListener();
        $listener->preUpdate($eventArguments);

        $this->assertEquals($expectedProbability, $opportunity->getProbability());
    }

    public function testShouldNotOverwriteProbabilityOnPersist()
    {
        $opportunity = $this->getOpportunity('negotiation', 0.25);
        $eventArguments = $this->getPrePersistEventArguments($opportunity);
        $listener = $this->getListener();
        $listener->prePersist($eventArguments);

        $this->assertEquals(0.25, $opportunity->getProbability());
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
        $listener->preUpdate($eventArguments);

        $this->assertEquals(0.25, $opportunity->getProbability());
        $this->assertEquals(0.25, $eventArguments->getNewValue('probability'));
    }

    /**
     * @dataProvider statusDataProvider
     *
     * @param string $statusId
     * @param float $expectedProbability
     */
    public function testShouldOverwriteEmptyProbabilityOnUpdate($statusId, $expectedProbability)
    {
        $opportunity = $this->getOpportunity($statusId);
        $eventArguments = $this->getPreUpdateEventArguments($opportunity, [
            'probability' => [0.1, null],
            'status' => [
                $this->getOpportunityStatus('negotiation'),
                $this->getOpportunityStatus('won')
            ]
        ]);
        $listener = $this->getListener();
        $listener->preUpdate($eventArguments);

        $this->assertEquals($expectedProbability, $eventArguments->getNewValue('probability'));
    }

    public function testShouldNotSetProbabilityWithoutStatusChangeOnUpdate()
    {
        $opportunity = $this->getOpportunity('negotiation', 0.25);
        $eventArguments = $this->getPreUpdateEventArguments($opportunity, [
            'probability' => [0.1, 0.25]
        ]);
        $listener = $this->getListener();
        $listener->preUpdate($eventArguments);

        $this->assertEquals(0.25, $opportunity->getProbability());
        $this->assertEquals(0.25, $eventArguments->getNewValue('probability'));
    }

    public function testShouldNotSetMissingDefaultProbabilityOnPersist()
    {
        $opportunity = $this->getOpportunity('unknown', 0.7);
        $eventArguments = $this->getPrePersistEventArguments($opportunity);
        $listener = $this->getListener();
        $listener->prePersist($eventArguments);

        $this->assertEquals(0.7, $opportunity->getProbability());
    }

    /**
     * @dataProvider statusDataProvider
     *
     * @param string $statusId
     */
    public function testShouldNotModifyRestrictedFieldsOnPersist($statusId)
    {
        $opportunity = $this->getOpportunity($statusId);
        $eventArguments = $this->getPrePersistEventArguments($opportunity);
        $listener = $this->getListener($restricted = true);
        $listener->prePersist($eventArguments);

        $this->assertNull($opportunity->getProbability());
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
        $listener = $this->getListener($restricted = true);
        $listener->preUpdate($eventArguments);

        $this->assertEquals(0.25, $opportunity->getProbability());
    }

    /**
     * @return array
     */
    public function statusDataProvider()
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
     * @param  bool $hasRestriction
     * @return DefaultProbabilityListener
     */
    private function getListener($hasRestriction = false)
    {
        $configManager = $this->getConfigManagerMock();
        $restrictionManager = $this->getRestrictionManagerMock($hasRestriction);
        $listener = new DefaultProbabilityListener($configManager, $restrictionManager);

        return $listener;
    }

    /**
     * @param object $object
     *
     * @return LifecycleEventArgs
     */
    private function getPrePersistEventArguments($object)
    {
        $entityManager = $this->getEntityManagerMock();

        $arguments = new LifecycleEventArgs($object, $entityManager);

        return $arguments;
    }

    /**
     * @param object $object
     * @param array $changeset
     *
     * @return PreUpdateEventArgs
     */
    private function getPreUpdateEventArguments($object, array $changeset = array())
    {
        $entityManager = $this->getEntityManagerMock();

        $arguments = new PreUpdateEventArgs($object, $entityManager, $changeset);

        return $arguments;
    }

    /**
     * @param string $statusId
     * @param float|null $probability
     *
     * @return Opportunity
     */
    private function getOpportunity($statusId, $probability = null)
    {
        $opportunity = new Opportunity();
        $opportunity->setStatus($this->getOpportunityStatus($statusId));
        $opportunity->setProbability($probability);

        return $opportunity;
    }

    /**
     * @param string $id
     *
     * @return AbstractEnumValue
     */
    private function getOpportunityStatus($id)
    {
        $enum = $this->getMockBuilder(AbstractEnumValue::class)
            ->disableOriginalConstructor()
            ->getMock();

        $enum->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $enum;
    }

    /**
     * @return ConfigManager
     */
    private function getConfigManagerMock()
    {
        $manager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())->method('get')
            ->will($this->returnValue($this->getDefaultProbilities()));

        return $manager;
    }

    /**
     * @param  bool $hasRestriction
     * @return RestrictionManager
     */
    private function getRestrictionManagerMock($hasRestriction = false)
    {
        $manager = $this->getMockBuilder(RestrictionManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())
            ->method('getEntityRestrictions')
            ->will($this->returnValue([['field' => 'probability']]));

        $manager->expects($this->any())
            ->method('hasEntityClassRestrictions')
            ->will($this->returnValue($hasRestriction));

        return $manager;
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManagerMock()
    {
        $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $meta = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $em = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $em->expects($this->any())->method('getClassMetadata')
            ->will($this->returnValue($meta));

        return $em;
    }
}
