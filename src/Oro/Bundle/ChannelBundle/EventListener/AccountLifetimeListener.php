<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ChannelBundle\Entity\Manager\LifetimeHistoryStatusUpdateManager;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Schedules accounts for deletion that has no customers assigned.
 */
class AccountLifetimeListener implements ServiceSubscriberInterface
{
    private ContainerInterface $container;
    private ?CurrencyQueryBuilderTransformerInterface $qbTransformer = null;
    private ?AccountCustomerManager $accountCustomerManager = null;
    /** @var Account[] */
    private array $accounts = [];
    private ?array $customerTargetFields = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_currency.query.currency_transformer' => CurrencyQueryBuilderTransformerInterface::class,
            'oro_sales.manager.account_customer' => AccountCustomerManager::class,
            'oro_channel.manager.lifetime_history_status_update' => LifetimeHistoryStatusUpdateManager::class,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($this->getChangedEntities($uow) as $entity) {
            if ($entity instanceof Opportunity) {
                $this->scheduleOpportunityAccount($entity, $uow);
            } elseif ($entity instanceof Customer) {
                $this->scheduleCustomerAccounts($entity, $uow);
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (!$this->accounts) {
            return;
        }

        $em = $args->getEntityManager();
        $lifetimeAmountQb = $this->getLifetimeAmountQueryBuilder($em);
        $historyUpdates = [];
        foreach ($this->accounts as $account) {
            $lifetimeAmountQb->setParameter('account', $account->getId());

            $lifetimeAmount = (double)$lifetimeAmountQb->getQuery()->getSingleScalarResult();

            $history = new LifetimeValueHistory();
            $history->setAmount($lifetimeAmount);
            $history->setAccount($account);
            $em->persist($history);

            $historyUpdates[] = [$account, null, $history];
        }

        $this->accounts = [];
        $em->flush();
        $this->sendHistoryUpdates($historyUpdates);
    }

    public function onClear(): void
    {
        $this->customerTargetFields = null;
    }

    private function getChangedEntities(UnitOfWork $uow): \Generator
    {
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            yield $entity;
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            yield $entity;
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            yield $entity;
        }
    }

    private function createNoCustomerCondition(string $customerAlias): string
    {
        return implode(
            ' AND ',
            array_map(
                function ($customerTargetField) use ($customerAlias) {
                    return sprintf('%s.%s IS NULL', $customerAlias, $customerTargetField);
                },
                $this->getCustomerTargetFields()
            )
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function scheduleOpportunityAccount(Opportunity $entity, UnitOfWork $uow): void
    {
        $customerAssociation = $entity->getCustomerAssociation();
        if (null === $customerAssociation) {
            return;
        }

        $account = $customerAssociation->getTarget();
        if (!$account instanceof Account) {
            return;
        }

        $changeSet = $uow->getEntityChangeSet($entity);
        if ($uow->isScheduledForDelete($entity)
            || (
                array_intersect(['closeRevenueValue', 'status', 'customerAssociation'], array_keys($changeSet))
                && (
                    ($entity->getStatus() && $entity->getStatus()->getId() === Opportunity::STATUS_WON)
                    || (
                        !empty($changeSet['status'][0])
                        && $changeSet['status'][0]->getId() === Opportunity::STATUS_WON
                    )
                )
            )
        ) {
            if (isset($changeSet['customerAssociation'])) {
                [$oldCustomer] = $changeSet['customerAssociation'];
                if ($oldCustomer && $oldCustomer->getTarget() instanceof Account) {
                    $oldAccount = $oldCustomer->getTarget();
                    if ($oldAccount->getId() !== $account->getId()) {
                        $this->scheduleAccount($oldAccount, $uow);
                    }
                }
            }
            $this->scheduleAccount($account, $uow);
        }
    }

    private function scheduleCustomerAccounts(Customer $entity, UnitOfWork $uow): void
    {
        $changeSet = $uow->getEntityChangeSet($entity);
        if (isset($changeSet['account'])) {
            [$oldAccount, $newAccount] = $changeSet['account'];
            if ($oldAccount) {
                $this->scheduleAccount($oldAccount, $uow);
            }
            if ($newAccount && (!$oldAccount || $oldAccount->getId() !== $newAccount->getId())) {
                $this->scheduleAccount($newAccount, $uow);
            }
        } elseif (array_intersect($this->getCustomerTargetFields(), array_keys($changeSet))) {
            $account = $entity->getAccount();
            $this->scheduleAccount($account, $uow);
        }
    }

    private function scheduleAccount(Account $account, UnitOfWork $uow): void
    {
        if ($uow->isScheduledForDelete($account)) {
            return;
        }

        $this->accounts[spl_object_hash($account)] = $account;
    }

    private function getLifetimeAmountQueryBuilder(EntityManager $em): QueryBuilder
    {
        $qb = $em->getRepository(Opportunity::class)->createQueryBuilder('o');
        $qb
            ->select(sprintf('SUM(%s)', $this->getQbTransformer()->getTransformSelectQuery('closeRevenue', $qb, 'o')))
            ->join('o.customerAssociation', 'c')
            ->andWhere('c.account = :account')
            ->andWhere('o.status = :status')
            ->setParameter('status', Opportunity::STATUS_WON);

        $noCustomerCondition = $this->createNoCustomerCondition('c');
        if ($noCustomerCondition) {
            $qb->andWhere($noCustomerCondition);
        }

        return $qb;
    }

    private function getCustomerTargetFields(): array
    {
        if (null === $this->customerTargetFields) {
            $this->customerTargetFields = $this->getAccountCustomerManager()->getCustomerTargetFields();
        }

        return $this->customerTargetFields;
    }

    private function getQbTransformer(): CurrencyQueryBuilderTransformerInterface
    {
        if (null === $this->qbTransformer) {
            $this->qbTransformer = $this->container->get('oro_currency.query.currency_transformer');
        }

        return $this->qbTransformer;
    }

    private function getAccountCustomerManager(): AccountCustomerManager
    {
        if (null === $this->accountCustomerManager) {
            $this->accountCustomerManager = $this->container->get('oro_sales.manager.account_customer');
        }

        return $this->accountCustomerManager;
    }

    private function sendHistoryUpdates(array $historyUpdates): void
    {
        $statusUpdateManager = $this->container->get('oro_channel.manager.lifetime_history_status_update');
        $statusUpdateManager->massUpdate($historyUpdates);
    }
}
