<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer as SalesCustomer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Component\DoctrineUtils\ORM\QueryUtils;

/**
 * This listener synchronizes account of MagentoCustomer and SalesCustomer
 *
 * It should be moved to crm-magento-bridge once it's created and thought about
 * possibility to remove this listener and use just one field without duplicating account information.
 */
class CustomerAccountChangeSubscriber implements EventSubscriber
{
    /** @var MagentoCustomer[] */
    protected $changedMagentoCustomers = [];

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
        $fixedSalesCustomers = $this->fixSalesCustomers($em, $this->changedMagentoCustomers);
        $this->changedMagentoCustomers = [];

        if ($fixedSalesCustomers) {
            $em->flush();
        }
    }

    /**
     * @param EntityManager $em
     * @param MagentoCustomer[] $changedMagentoCustomers
     *
     * @return SalesCustomer[] Fixed SalesCustomers
     */
    protected function fixSalesCustomers(EntityManager $em, array $changedMagentoCustomers)
    {
        $salesCustomersWithInvalidAccount = $this->findSalesCustomersWithInvalidAccount($em, $changedMagentoCustomers);
        foreach ($salesCustomersWithInvalidAccount as $customer) {
            $customer->setTarget($customer->getTarget());
        }

        return $salesCustomersWithInvalidAccount;
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
    protected function findSalesCustomersWithInvalidAccount(EntityManager $em, array $customers)
    {
        if (!$customers) {
            return [];
        }

        $qb = $em->getRepository(SalesCustomer::class)->createQueryBuilder('sc');

        $magentoCustomerField = ExtendHelper::buildAssociationName(
            MagentoCustomer::class,
            CustomerScope::ASSOCIATION_KIND
        );

        foreach ($customers as $customer) {
            $exprs = [];

            $customerParam = QueryUtils::generateParameterName('magentoCustomer');
            $exprs[] = $qb->expr()->eq(
                sprintf('sc.%s', $magentoCustomerField),
                sprintf(':%s', $customerParam)
            );
            $qb->setParameter($customerParam, $customer->getId());

            $exprs[] = $this->createAccountExpr($qb, 'sc', $customer->getAccount());

            $qb->andWhere(call_user_func_array([$qb->expr(), 'andX'], $exprs));
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param string $alias
     * @param Account $account
     *
     * @return mixed
     */
    protected function createAccountExpr(QueryBuilder $qb, $alias, Account $account = null)
    {
        if (!$account) {
            return $qb->expr()->isNotNull(sprintf('%s.account', $alias));
        }

        $accountParam = QueryUtils::generateParameterName('account');
        $qb->setParameter($accountParam, $account->getId());

        return $qb->expr()->orX(
            $qb->expr()->neq(sprintf('%s.account', $alias), sprintf(':%s', $accountParam)),
            $qb->expr()->isNull(sprintf('%s.account', $alias))
        );
    }
}
