<?php

namespace OroCRM\Bundle\SalesBundle\Dashboard\Converters;

use Oro\Bundle\DashboardBundle\Provider\Converters\WidgetEntitySelectConverter;

use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

class WidgetLeadStatusSelectConverter extends WidgetEntitySelectConverter
{
    /**
     * {@inheritdoc}
     */
    public function getViewValue($value)
    {
        $entities = $this->getEntities($value);

        $names = [];
        /** @var LeadStatus $entity */
        foreach ($entities as $entity) {
            $names[] = $entity->getLabel();
        }

        return empty($names) ? null : implode('; ', $names);
    }
}
