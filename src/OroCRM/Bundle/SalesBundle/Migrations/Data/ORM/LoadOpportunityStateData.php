<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class LoadOpportunityStateData extends AbstractEnumFixture
{
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
}
