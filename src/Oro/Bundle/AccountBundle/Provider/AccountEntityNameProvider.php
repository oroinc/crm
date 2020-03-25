<?php

namespace Oro\Bundle\AccountBundle\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\Provider\EntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

/**
 * Account entity name should be equal only to the value of its name field
 */
class AccountEntityNameProvider implements EntityNameProviderInterface
{
    /** @var EntityNameProvider */
    private $defaultEntityNameProvider;

    public function __construct(EntityNameProvider $defaultEntityNameProvider)
    {
        $this->defaultEntityNameProvider = $defaultEntityNameProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getName($format, $locale, $entity)
    {
        if ($format === EntityNameProviderInterface::FULL && is_a($entity, Account::class, true)) {
            return (string)$entity->getName();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        if (!is_a($className, Account::class, true)) {
            return false;
        }

        return $this->defaultEntityNameProvider->getNameDQL(
            EntityNameProviderInterface::SHORT,
            $locale,
            $className,
            $alias
        );
    }
}
