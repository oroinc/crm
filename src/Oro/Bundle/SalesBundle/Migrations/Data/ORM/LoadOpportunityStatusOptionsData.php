<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Load opportunity status enum option data.
 */
class LoadOpportunityStatusOptionsData extends AbstractEnumFixture
{
    protected function getData(): array
    {
        return [
            'in_progress' => 'Open',
            'identification_alignment' => 'Identification & Alignment',
            'needs_analysis' => 'Needs Analysis',
            'solution_development' => 'Solution Development',
            'negotiation' => 'Negotiation',
            'won' => 'Closed Won',
            'lost' => 'Closed Lost',
        ];
    }

    protected function getEnumCode(): string
    {
        return 'opportunity_status';
    }

    protected function getDefaultValue(): string
    {
        return 'in_progress';
    }
}
