<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\AddressBundle\Entity\Region;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Region as MagentoRegion;

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

    /**
     * Get existing registered customer or existing guest customer
     * Find existing customer using entity data for entities containing customer like Order and Cart
     *
     * @param object $entity
     *
     * @return null|Customer
     */
    protected function findExistingCustomerByContext($entity)
    {
        $customer = $entity->getCustomer();

        if ($customer instanceof Customer) {
            /** @var Customer|null $existingEntity */
            $searchContext = $this->getEntityCustomerSearchContext($entity);
            $existingEntity = $this->databaseHelper->findOneBy(
                'OroCRM\Bundle\MagentoBundle\Entity\Customer',
                $searchContext
            );

            return $existingEntity;
        }

        return null;
    }

    /**
     * Get search context for entity customer
     *
     * @param object $entity
     *
     * @return array
     */
    protected function getEntityCustomerSearchContext($entity)
    {
        $searchContext = [];

        $customer = $entity->getCustomer();
        if ($customer instanceof Customer) {
            $searchContext = $this->getCustomerSearchContext($customer);
        }

        return  $searchContext;
    }

    /**
     * Get customer search context by channel and website if exists
     *
     * @param Customer $customer
     *
     * @return array
     */
    protected function getCustomerSearchContext(Customer $customer)
    {
        $searchContext = [
            'channel' => $customer->getChannel()
        ];

        if ($customer->getWebsite()) {
            $website = $this->databaseHelper->findOneBy(
                'OroCRM\Bundle\MagentoBundle\Entity\Website',
                [
                    'originId' => $customer->getWebsite()->getOriginId(),
                    'channel' => $customer->getChannel()
                ]
            );
            if ($website) {
                $searchContext['website'] = $website;
            }
        }

        return $searchContext;
    }

    /**
     * Find existing Magento Region entity
     *
     * @param Region $entity
     *
     * @return null|MagentoRegion
     */
    protected function findRegionEntity(Region $entity)
    {
        $existingEntity = null;

        /** @var Region $magentoRegion */
        $magentoRegion = $this->databaseHelper->findOneBy(
            'OroCRM\Bundle\MagentoBundle\Entity\Region',
            [
                'regionId' => $entity->getCombinedCode()
            ]
        );
        if ($magentoRegion) {
            $existingEntity = $this->databaseHelper->findOneBy(
                'Oro\Bundle\AddressBundle\Entity\Region',
                [
                    'combinedCode' => $magentoRegion->getCombinedCode()
                ]
            );
        }

        return $existingEntity;
    }
}
