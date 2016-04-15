<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class LoadOpportunityStateData extends AbstractEnumFixture implements DependentFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['OroCRM\Bundle\SalesBundle\Migrations\Data\ORM\LoadOpportunityStatusData'];
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $statuses = [
            'identification_alignment' => 'Identification & Alignment',
            'needs_analysis' => 'Needs Analysis',
            'solution_development' => 'Solution Development',
            'negotiation' => 'Negotiation',
            'won' => 'Closed Won',
            'lost' => 'Closed Lost'
        ];

        $oldStatuses = $this->manager
            ->getRepository('OroCRMSalesBundle:OpportunityStatus')
            ->findAll();

        foreach ($oldStatuses as $oldStatus) {
            $oldName = $oldStatus->getName();
            $oldLabel = $oldStatus->getLabel();

            if (!array_key_exists($oldName, $statuses)) {
                $statuses[$oldName] = $oldLabel;
            }
        }

        return $statuses;
    }

    /**
     * @return string
     */
    protected function getEnumCode()
    {
        return Opportunity::INTERNAL_STATE_CODE;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        parent::load($manager);
    }
}
