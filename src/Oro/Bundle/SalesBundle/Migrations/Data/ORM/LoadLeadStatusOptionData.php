<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Load lead status enum options data.
 */
class LoadLeadStatusOptionData extends AbstractEnumFixture
{
    #[\Override]
    protected function getData(): array
    {
        return [
            'new' => 'New',
            'qualified' => 'Qualified',
            'canceled' => 'Disqualified',
        ];
    }

    #[\Override]
    protected function getDefaultValue(): string
    {
        return 'new';
    }

    #[\Override]
    protected function getEnumCode(): string
    {
        return 'lead_status';
    }
}
