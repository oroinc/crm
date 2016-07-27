<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

/**
 * Represents B2bCustomer entities by 'name' field avoiding usage of FullNameInterface.
 *
 * @TODO this class is a workaround and should be removed after implementation of
 * entity name representation configuration.
 */
class B2bCustomerEntityNameProvider implements EntityNameProviderInterface
{
    const CLASS_NAME = 'OroCRM\Bundle\SalesBundle\Entity\B2bCustomer';

    /**
     * {@inheritdoc}
     */
    public function getName($format, $locale, $entity)
    {
        if ($format === EntityNameProviderInterface::FULL && is_a($entity, self::CLASS_NAME)) {
            return $entity->getName();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        if ($format === EntityNameProviderInterface::FULL && $className === self::CLASS_NAME) {
            return sprintf('%s.name', $alias);
        }

        return false;
    }
}
