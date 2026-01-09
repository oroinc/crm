<?php

namespace Oro\Bundle\SalesBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;

/**
 * Import strategy specific for Lead entity.
 */
class LeadAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * Since addresses and phones are always unique for a specific lead and do not have unique identifiers,
     * there is no point in using them again. Also see method: storeNewEntity
     *
     */
    #[\Override]
    protected function findEntityByIdentityValues($entityName, array $identityValues): ?object
    {
        if (
            is_a($entityName, LeadPhone::class, true)
            || is_a($entityName, LeadAddress::class, true)
        ) {
            return null;
        }

        return parent::findEntityByIdentityValues($entityName, $identityValues);
    }

    #[\Override]
    protected function storeNewEntity(object $entity, ?array $identityValues = null): ?object
    {
        if (is_a($entity, LeadPhone::class, true) || is_a($entity, LeadAddress::class, true)) {
            return null;
        }

        return parent::storeNewEntity($entity, $identityValues);
    }
}
