<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Handler\OpportunityApiHandler;

class OpportunityApiHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider probabilityDataProvider
     *
     * @param float $probability
     */
    public function testShouldAppendProbabilityToChangeset($probability)
    {
        $changeset = [
            'fields' => [
                'probability' => $probability,
            ],
        ];

        $handler = $this->getOpportunityApiHndler([]);
        $opportunity = $this->getOpportunity($probability);

        $this->assertEquals($changeset, $handler->afterProcess($opportunity));
    }

    /**
     * @return array
     */
    public function probabilityDataProvider()
    {
        return [
            [0.0], [0.1], [0.5], [1.0]
        ];
    }

    /**
     * @dataProvider statusDataProvider
     *
     * @param string $statusId
     * @param float $expectedProbability
     */
    public function testStatusChangeShouldSetDefaultProbability($statusId, $expectedProbability)
    {
        $changeset = [
            'status' => [
                null, //old
                $this->getOpportunityStatus($statusId), //new
            ]
        ];

        $handler = $this->getOpportunityApiHndler($changeset);
        $opportunity = $this->getOpportunity(0);

        $handler->beforeProcess($opportunity);

        $this->assertEquals($expectedProbability, $opportunity->getProbability());
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

    public function testStatusAndProbabilityChangeShouldNotSetDefaultProbability()
    {
        $changeset = [
            'status' => [
                null, //old
                $this->getOpportunityStatus('won'), //new
            ],
            'probability' => [
                0,
                0,
            ]
        ];

        $handler = $this->getOpportunityApiHndler($changeset);
        $opportunity = $this->getOpportunity(0.7);

        $handler->beforeProcess($opportunity);

        $this->assertEquals(0.7, $opportunity->getProbability());
    }

    public function testStatusWithoutDefaultShouldNotSetDefaultProbability()
    {
        $changeset = [
            'status' => [
                null, //old
                $this->getOpportunityStatus('unknown'), //new
            ],
        ];

        $handler = $this->getOpportunityApiHndler($changeset);
        $opportunity = $this->getOpportunity(0.3);

        $handler->beforeProcess($opportunity);

        $this->assertEquals(0.3, $opportunity->getProbability());
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
     * @param array $changeset
     * @return OpportunityApiHandler
     */
    private function getOpportunityApiHndler(array $changeset)
    {
        $entityManager = $this->getEntityManagerMock($changeset);
        $configManager = $this->getConfigManagerMock();

        return new OpportunityApiHandler($entityManager, $configManager);
    }

    /**
     * @param float $probability
     * @return Opportunity
     */
    private function getOpportunity($probability)
    {
        $opportunity = new Opportunity();
        $opportunity->setProbability($probability);

        return $opportunity;
    }

    /**
     * @param string $id
     * @return AbstractEnumValue
     */
    private function getOpportunityStatus($id)
    {
        $enum = $this->getMockBuilder('Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue')
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
        $manager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $manager->expects($this->any())->method('get')
            ->will($this->returnValue($this->getDefaultProbilities()));

        return $manager;
    }

    /**
     * @param array $changeset
     * @return EntityManager
     */
    private function getEntityManagerMock(array $changeset)
    {
        $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $uow->expects($this->any())->method('getEntityChangeSet')
            ->will($this->returnValue($changeset));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->any())->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        return $em;
    }
}
