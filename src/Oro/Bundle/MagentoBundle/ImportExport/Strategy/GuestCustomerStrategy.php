<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\GuestCustomerStrategyHelper;

class GuestCustomerStrategy extends AbstractImportStrategy
{
    /**
     * ID of group for not logged customers
     */
    const NOT_LOGGED_IN_ID = 0;

    /**
     * @var GuestCustomerStrategyHelper
     */
    private $guestCustomerStrategyHelper;

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
        $entity = $this->processEntity(
            $entity,
            true,
            true,
            $this->context->getValue('itemData')
        );
        $entity = $this->afterProcessEntity($entity);
        if ($entity) {
            $entity = $this->validateAndUpdateContext($entity);
        }

        return $entity;
    }

    /**
     * @param GuestCustomerStrategyHelper $strategyHelper
     */
    public function setGuestCustomerStrategyHelper(GuestCustomerStrategyHelper $strategyHelper)
    {
        $this->guestCustomerStrategyHelper = $strategyHelper;
    }

    /**
     * @param Customer $entity
     *
     * @return Customer|null
     */
    protected function checkExistingCustomer(Customer $entity)
    {
        if (!$entity->getEmail()) {
            return null;
        }

        $searchContext = $this->getSearchContext($entity);
        /** @var Customer $existingCustomer */
        $existingCustomer = $this->databaseHelper->findOneBy(
            Customer::class,
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

        $searchContext = $this->guestCustomerStrategyHelper->getUpdatedSearchContextForGuestCustomers(
            $entity,
            $searchContext
        );

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
     * {@inheritdoc}
     */
    protected function findExistingEntityByIdentityFields($entity, array $searchContext = [])
    {
        if ($entity instanceof Customer && !$entity->getOriginId()) {
            $searchContext['email'] = $entity->getEmail();
            $searchContext = $this->guestCustomerStrategyHelper->getUpdatedSearchContextForGuestCustomers(
                $entity,
                $searchContext
            );
        }

        return parent::findExistingEntityByIdentityFields($entity, $searchContext);
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
            $searchContext = $this->guestCustomerStrategyHelper->getUpdatedSearchContextForGuestCustomers(
                $entity,
                $searchContext
            );
        }

        return parent::combineIdentityValues($entity, $entityClass, $searchContext);
    }
}
