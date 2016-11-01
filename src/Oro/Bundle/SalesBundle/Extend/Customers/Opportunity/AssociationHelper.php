<?php

namespace Oro\Bundle\SalesBundle\Extend\Customers\Opportunity;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class AssociationHelper
{
    const OPPORTUNITY_ENTITY = 'Oro\Bundle\SalesBundle\Entity\Opportunity';

    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * Checks whether opportunities are enabled for a given entity type.
     *
     * @param string $entityClass The target entity class
     * @param bool $accessible    Whether an association with the target entity should be checked
     *                            to be ready to use in a business logic.
     *                            It means that the association should exist and should not be marked as deleted.
     *
     * @return bool
     */
    public function isAssociationEnabled($entityClass, $accessible = true)
    {
        if (!$this->configManager->hasConfig($entityClass)) {
            return false;
        }
        if (!$this->configManager->getEntityConfig('sales_opportunity', $entityClass)->is('enabled')) {
            return false;
        }

        return
            !$accessible
            || $this->isAssociationAccessible($entityClass);
    }

    /**
     * Check if an association between a given entity type and opportunities is ready to be used in a business logic.
     * It means that the association should exist and should not be marked as deleted.
     *
     * @param string $entityClass The target entity class
     *
     * @return bool
     */
    protected function isAssociationAccessible($entityClass)
    {
        $associationName = ExtendHelper::buildAssociationName($entityClass);
        if (!$this->configManager->hasConfig(self::OPPORTUNITY_ENTITY, $associationName)) {
            return false;
        }

        return ExtendHelper::isFieldAccessible(
            $this->configManager->getFieldConfig('extend', self::OPPORTUNITY_ENTITY, $associationName)
        );
    }
}
