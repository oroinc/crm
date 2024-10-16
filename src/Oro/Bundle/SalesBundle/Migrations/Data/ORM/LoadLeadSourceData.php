<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

/**
 * Loads Lead source enum options
 */
class LoadLeadSourceData extends AbstractEnumFixture
{
    #[\Override]
    protected function getData(): array
    {
        return [
            $this->getDefaultValue() => 'Demand Generation'
        ];
    }

    #[\Override]
    protected function getDefaultValue(): string
    {
        return ExtendHelper::buildEnumInternalId('Demand Generation');
    }

    #[\Override]
    protected function getEnumCode(): string
    {
        return 'lead_source';
    }
}
