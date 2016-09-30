<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\MagentoBundle\Entity\Customer;

class GuestCustomerStrategy extends AbstractImportStrategy
{
    /**
     * ID of group for not logged customers
     */
    const NOT_LOGGED_IN_ID = 0;

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        if ($this->checkExistingCustomer($entity)) {
            return null;
        }

        $this->cachedEntities = [];
        $entity = $this->beforeProcessEntity($entity);
        $entity = $this->processEntity($entity, true, true, $this->context->getValue('itemData'));
        $entity = $this->afterProcessEntity($entity);
        if ($entity) {
            $entity = $this->validateAndUpdateContext($entity);
        }

        return $entity;
    }

    /**
     * @param Customer $entity
     *
     * @return Customer
     */
    protected function checkExistingCustomer(Customer $entity)
    {
        if (!$entity->getEmail()) {
            return null;
        }

        $searchContext = $this->getSearchContext($entity);
        $existingCustomer = $this->databaseHelper->findOneBy(
            'Oro\Bundle\MagentoBundle\Entity\Customer',
            $searchContext
        );

        return $existingCustomer;
    }

    /**
     * Search Guest customer by email, channel and website if exists
     *
     * @param Customer $entity
     * @return array
     */
    protected function getSearchContext(Customer $entity)
    {
        $searchContext = parent::getCustomerSearchContext($entity);
        $searchContext['email'] = $entity->getEmail();

        return $searchContext;
    }

    /**
     * @param Customer $entity
     *
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        $this->processChangeAttributes($entity);

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Customer $entity
     */
    protected function processChangeAttributes(Customer $entity)
    {
        foreach ($entity->getAddresses() as $address) {
            $address->setOriginId(null);
        }

        $customerStore = $entity->getStore();
        if ($customerStore) {
            $website = $customerStore->getWebsite();

            $entity->setWebsite($website);
            $this->setDefaultGroup($entity);
        }
    }

    /**
     * @param Customer $entity
     */
    protected function setDefaultGroup(Customer $entity)
    {
        if (!$entity->getGroup()) {
            $em = $this->strategyHelper->getEntityManager('OroMagentoBundle:CustomerGroup');
            $group = $em->getRepository('OroMagentoBundle:CustomerGroup')
                ->findOneBy(
                    [
                        'originId' => static::NOT_LOGGED_IN_ID,
                        'channel' => $entity->getChannel()
                    ]
                );
            $entity->setGroup($group);
        }
    }

    /**
     * Specify Customer Email as identity field for Guest Customer
     *
     * Guest Customer created from Order data and not exist in Magento as entity so don't have originId
     * Specified additional identity
     *
     * @param string $entityName
     * @param array $identityValues
     * @return null|object
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        if (is_a($entityName, 'Oro\Bundle\MagentoBundle\Entity\Customer', true)
            && empty($identityValues['originId'])
        ) {
            $data = $this->context->getValue('itemData');
            if (isset($data['email'])) {
                $identityValues['email'] = $data['email'];
            }
        }

        return parent::findEntityByIdentityValues($entityName, $identityValues);
    }

    /**
     * Combine Customer Email with identity values for search existing customer entity
     *
     * Added special search context for Guest Customer entities not existing in Magento (without originId)
     *
     * @param object $entity
     * @param string $entityClass
     * @param array $searchContext
     * @return array|null
     */
    protected function combineIdentityValues($entity, $entityClass, array $searchContext)
    {
        if ($entity instanceof Customer && !$entity->getOriginId()) {
            $searchContext['email'] = $entity->getEmail();
        }

        return parent::combineIdentityValues($entity, $entityClass, $searchContext);
    }
}
