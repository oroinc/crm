<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ChannelBundle\Entity\Manager\LifetimeHistoryStatusUpdateManager;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
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
    private ?CurrencyQueryBuilderTransformerInterface $qbTransformer = null;
    private ?AccountCustomerManager $accountCustomerManager = null;
    /** @var Account[] */
    private array $accounts = [];
    private ?array $customerTargetFields = null;

    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            CurrencyQueryBuilderTransformerInterface::class,
            AccountCustomerManager::class,
            LifetimeHistoryStatusUpdateManager::class
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
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

        $em = $args->getObjectManager();
        $lifetimeAmountQb = $this->getLifetimeAmountQueryBuilder($em);
        $historyUpdates = [];
        foreach ($this->accounts as $account) {
            $lifetimeAmountQb->setParameter('account', $account->getId());

            $lifetimeAmount = (float)$lifetimeAmountQb->getQuery()->getSingleScalarResult();

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
        if (
            $uow->isScheduledForDelete($entity)
            || (
                array_intersect(['closeRevenueValue', 'serialized_data', 'customerAssociation'], array_keys($changeSet))
                && (
                    ($entity->getStatus() && $entity->getStatus()->getInternalId() === Opportunity::STATUS_WON)
                    || (
                        !empty($changeSet['serialized_data'][0]['status'])
                        && $changeSet['serialized_data'][0]['status'] === ExtendHelper::buildEnumOptionId(
                            Opportunity::INTERNAL_STATUS_CODE,
                            Opportunity::STATUS_WON
                        )
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
            $this->scheduleAccount($entity->getAccount(), $uow);
        }
    }

    private function scheduleAccount(Account $account, UnitOfWork $uow): void
    {
        if ($uow->isScheduledForDelete($account)) {
            return;
        }

        $this->accounts[spl_object_hash($account)] = $account;
    }

    private function getLifetimeAmountQueryBuilder(EntityManagerInterface $em): QueryBuilder
    {
        $qb = $em->createQueryBuilder();
        $qb
            ->from(Opportunity::class, 'o')
            ->select(sprintf('SUM(%s)', $this->getQbTransformer()->getTransformSelectQuery('closeRevenue', $qb, 'o')))
            ->join('o.customerAssociation', 'c')
            ->andWhere('c.account = :account')
            ->andWhere("JSON_EXTRACT(o.serialized_data, 'status') = :status")
            ->setParameter(
                'status',
                ExtendHelper::buildEnumOptionId(Opportunity::INTERNAL_STATUS_CODE, Opportunity::STATUS_WON)
            );

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
            $this->qbTransformer = $this->container->get(CurrencyQueryBuilderTransformerInterface::class);
        }

        return $this->qbTransformer;
    }

    private function getAccountCustomerManager(): AccountCustomerManager
    {
        if (null === $this->accountCustomerManager) {
            $this->accountCustomerManager = $this->container->get(AccountCustomerManager::class);
        }

        return $this->accountCustomerManager;
    }

    private function sendHistoryUpdates(array $historyUpdates): void
    {
        /** @var LifetimeHistoryStatusUpdateManager $statusUpdateManager */
        $statusUpdateManager = $this->container->get(LifetimeHistoryStatusUpdateManager::class);
        $statusUpdateManager->massUpdate($historyUpdates);
    }
}
