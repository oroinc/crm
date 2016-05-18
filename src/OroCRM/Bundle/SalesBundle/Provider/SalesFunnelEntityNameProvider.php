<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

class SalesFunnelEntityNameProvider implements EntityNameProviderInterface
{
    const CLASS_NAME = 'OroCRM\Bundle\SalesBundle\Entity\SalesFunnel';

    /**
     * {@inheritdoc}
     */
    public function getName($format, $locale, $entity)
    {
        if ($format === EntityNameProviderInterface::FULL && is_a($entity, self::CLASS_NAME)) {
            return $entity->getFirstName();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        if ($format === EntityNameProviderInterface::FULL && $className === self::CLASS_NAME) {
            return sprintf(
                'CONCAT(\'Sales Funnel \', %s.id)',
                $alias
            );
        }

        return false;
    }
}
