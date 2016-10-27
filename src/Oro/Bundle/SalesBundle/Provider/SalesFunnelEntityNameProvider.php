<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

/**
 * @deprecated since 1.10 will be removed after 2.1
 */
class SalesFunnelEntityNameProvider implements EntityNameProviderInterface
{
    const CLASS_NAME = 'Oro\Bundle\SalesBundle\Entity\SalesFunnel';

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
