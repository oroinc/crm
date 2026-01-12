<?php

namespace Oro\Bundle\ActivityContactBundle\Placeholder;

use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use Oro\Bundle\ActivityListBundle\Placeholder\PlaceholderFilter as BasePlaceholder;

/**
 * Filters placeholder visibility based on activity contact relationship configuration.
 */
class PlaceholderFilter extends BasePlaceholder
{
    #[\Override]
    public function isApplicable($entity = null, $pageType = null)
    {
        $entityClass = $this->doctrineHelper->getEntityClass($entity);
        if (TargetExcludeList::isExcluded($entityClass)) {
            return false;
        }

        return parent::isApplicable($entity, $pageType);
    }
}
