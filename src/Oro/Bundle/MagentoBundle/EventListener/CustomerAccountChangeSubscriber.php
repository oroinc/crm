<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer as SalesCustomer;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountCustomerHelper;

/**
 * This listener synchronizes account of MagentoCustomer and SalesCustomer
 *
 * It should be moved to crm-magento-bridge once it's created and thought about
 * possibility to remove this listener and use just one field without duplicating account information.
 */
class CustomerAccountChangeSubscriber implements EventSubscriber
{
    /** @var AccountCustomerHelper */
    protected $accountCustomerHelper;

    /** @var MagentoCustomer[] */
    protected $changedMagentoCustomers = [];

    /**
     * @param AccountCustomerHelper    $helper
     */
    public function __construct(AccountCustomerHelper $helper)
    {
        $this->accountCustomerHelper     = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'postFlush',
        ];
    }

    /**
     * Stores MagentoCustomers with changed Account
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();
        $this->prepareChangedMagentoCustomers(
            $uow,
            array_merge(
                $uow->getScheduledEntityInsertions(),
                $uow->getScheduledEntityUpdates()
            )
        );
    }

    /**
     * Syncs Accounts of MagentoCustomers and SalesCustomers
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->changedMagentoCustomers) {
            return;
        }

        $em = $args->getEntityManager();
        $syncedSalesCustomers = $this->syncSalesCustomersAccounts($em, $this->changedMagentoCustomers);
        $this->changedMagentoCustomers = [];

        if ($syncedSalesCustomers) {
            $em->flush();
        }
    }

    /**
     * @param EntityManager $em
     * @param MagentoCustomer[] $changedMagentoCustomers
     *
     * @return SalesCustomer[] Fixed SalesCustomers
     */
    protected function syncSalesCustomersAccounts(EntityManager $em, array $changedMagentoCustomers)
    {
        $salesCustomersWithChangedAccount = $this->findSalesCustomersWithChangedAccount($em, $changedMagentoCustomers);
        foreach ($salesCustomersWithChangedAccount as $customer) {
            $this->accountCustomerHelper->syncTargetCustomerAccount($customer);
        }

        return $salesCustomersWithChangedAccount;
    }

    /**
     * @param UnitOfWork $uow
     * @param object[]   $entities
     */
    protected function prepareChangedMagentoCustomers(UnitOfWork $uow, array $entities)
    {
        foreach ($entities as $oid => $entity) {
            if (!$entity instanceof MagentoCustomer) {
                continue;
            }

            $changeSet = $uow->getEntityChangeSet($entity);
            if (!isset($changeSet['account'])) {
                continue;
            }

            $this->changedMagentoCustomers[$oid] = $entity;
        }
    }

    /**
     * @param EntityManager $em
     * @param MagentoCustomer[] $customers
     *
     * @return SalesCustomer[]
     */
    protected function findSalesCustomersWithChangedAccount(EntityManager $em, array $customers)
    {
        $customerRepository = $this->getAccountCustomerRepository($em);

        $magentoCustomers = array_map(
            function (Customer $customer) {
                $account = $customer->getAccount();

                return [
                    'target_id'  => $customer->getId(),
                    'account_id' => $account ? $account->getId() : null
                ];
            },
            $customers
        );

        return $customerRepository->getSalesCustomersWithChangedAccount(
            [MagentoCustomer::class => $magentoCustomers]
        );
    }

    /**
     * @param EntityManager $em
     *
     * @return CustomerRepository
     */
    protected function getAccountCustomerRepository(EntityManager $em)
    {
        return $em->getRepository(SalesCustomer::class);
    }
}
