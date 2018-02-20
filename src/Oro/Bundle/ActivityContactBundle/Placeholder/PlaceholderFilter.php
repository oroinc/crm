<?php

namespace Oro\Bundle\ActivityContactBundle\Placeholder;

use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use Oro\Bundle\ActivityListBundle\Placeholder\PlaceholderFilter as BasePlaceholder;

class PlaceholderFilter extends BasePlaceholder
{
    /** {@inheritdoc} */
    public function isApplicable($entity = null, $pageType = null)
    {
        $entityClass = $this->doctrineHelper->getEntityClass($entity);
        if (TargetExcludeList::isExcluded($entityClass)) {
            return false;
        }

        return parent::isApplicable($entity, $pageType);
    }
}
