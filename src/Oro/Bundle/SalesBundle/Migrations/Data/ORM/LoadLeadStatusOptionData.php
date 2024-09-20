<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Load lead status enum options data.
 */
class LoadLeadStatusOptionData extends AbstractEnumFixture
{
    protected function getData(): array
    {
        return [
            'new' => 'New',
            'qualified' => 'Qualified',
            'canceled' => 'Disqualified',
        ];
    }

    protected function getDefaultValue(): string
    {
        return 'new';
    }

    protected function getEnumCode(): string
    {
        return 'lead_status';
    }
}
