<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class LoadOpportunityStateData extends AbstractEnumFixture
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @return array
     */
    protected function getData()
    {
        return [
            'identification_alignment' => 'Identification & Alignment',
            'needs_analysis' => 'Needs Analysis',
            'solution_development' => 'Solution Development',
            'negotiation' => 'Negotiation',
            'won' => 'Closed Won',
            'lost' => 'Closed Lost'
        ];
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
        $stateClassName = ExtendHelper::buildEnumValueClassName($this->getEnumCode());
        $this->manager = $manager;
        parent::load($manager);

        $data = $this->getData();
        $opportunityList = $manager->getRepository('OroCRMSalesBundle:Opportunity')->findAll();

        $isNeedFlush = false;
        foreach ($opportunityList as $opportunity) {
            $status = $opportunity->getStatus()->getName();
            if ($status && array_key_exists($status, $data)) {
                $opportunity->setState($manager->getReference($stateClassName, $status));
                $isNeedFlush = true;
            } elseif ($status === 'in_progress') {
                $opportunity->setState($manager->getReference($stateClassName, 'solution_development'));
                $isNeedFlush = true;
            }
        }

        if ($isNeedFlush) {
            $manager->flush();
        }
    }
}
