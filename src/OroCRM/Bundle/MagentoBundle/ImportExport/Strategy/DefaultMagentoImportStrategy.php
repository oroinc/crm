<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class DefaultMagentoImportStrategy extends ConfigurableAddOrReplaceStrategy
{
    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /**
     * @param PropertyAccessor $propertyAccessor
     */
    public function setPropertyAccessor(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateContextCounters($entity)
    {
        // increment context counter
        $identifier = $this->databaseHelper->getIdentifier($entity);
        if ($identifier) {
            $this->context->incrementUpdateCount();
        } else {
            $this->context->incrementAddCount();
        }

        return $entity;
    }

    /**
     * Specify channel as identity field
     *
     * For local entities created from not existing in Magento entities (as guest customer - without originId)
     * should be specified additional identities in appropriate strategy
     *
     * @param string $entityName
     * @param array $identityValues
     * @return null|object
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        if (is_a($entityName, 'OroCRM\Bundle\MagentoBundle\Entity\IntegrationAwareInterface', true)) {
            $identityValues['channel'] = $this->context->getOption('channel');
        }

        return parent::findEntityByIdentityValues($entityName, $identityValues);
    }

    /**
     * Combine channel with identity values for entity search on local new entities storage
     *
     * For local entities created from not existing in Magento entities (as guest customer - without originId)
     * should be configured special identity fields or search context in appropriate strategy
     *
     * @param       $entity
     * @param       $entityClass
     * @param array $searchContext
     *
     * @return array|null
     */
    protected function combineIdentityValues($entity, $entityClass, array $searchContext)
    {
        if (is_a($entityClass, 'OroCRM\Bundle\MagentoBundle\Entity\IntegrationAwareInterface', true)) {
            $searchContext['channel'] = $this->context->getOption('channel');
        }

        return parent::combineIdentityValues($entity, $entityClass, $searchContext);
    }

    /**
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if (!$this->propertyAccessor) {
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }
        return $this->propertyAccessor;
    }
}
