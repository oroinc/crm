<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Region as MagentoRegion;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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
        if (is_a($entityName, 'Oro\Bundle\MagentoBundle\Entity\IntegrationAwareInterface', true)) {
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
        if (is_a($entityClass, 'Oro\Bundle\MagentoBundle\Entity\IntegrationAwareInterface', true)) {
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
     * Get existing registered customer
     * Find existing customer using entity data for entities containing customer like Order and Cart
     *
     * @param object $entity
     *
     * @return null|Customer
     */
    protected function findExistingCustomerByContext($entity)
    {
        /** @var Customer|null $existingEntity */
        $searchContext = $this->getEntityCustomerSearchContext($entity);
        $existingEntity = $this->databaseHelper->findOneBy(
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            $searchContext
        );

        return $existingEntity;
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
        $customer = $entity->getCustomer();
        if ($customer instanceof Customer) {
            $searchContext = $this->getCustomerSearchContext($customer);
        } else {
            $searchContext = [
                'channel' => $entity->getChannel()
            ];
        }

        return $searchContext;
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
                'Oro\Bundle\MagentoBundle\Entity\Website',
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
     * Find existing Oro Region entity
     * Find by Code if parameter $regionId not passed
     *
     * @param Region      $entity
     * @param string|null $mageRegionId
     *
     * @return null|Region
     */
    protected function findRegionEntity(Region $entity, $mageRegionId = null)
    {
        $existingEntity = null;

        if (!$mageRegionId) {
            $mageRegionId = $entity->getCode();
        }

        /** @var MagentoRegion $magentoRegion */
        $magentoRegion = $this->databaseHelper->findOneBy(
            'Oro\Bundle\MagentoBundle\Entity\Region',
            [
                'regionId' => $mageRegionId
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
