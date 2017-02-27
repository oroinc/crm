<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class AccountLifetimeSubscriber implements EventSubscriber
{
    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /** @var CurrencyQueryBuilderTransformerInterface $currencyQbTransformer */
    protected $currencyQbTransformer;

    /** @var Account[] */
    protected $accounts = [];

    /**
     * @param AccountCustomerManager $accountCustomerManager
     * @param CurrencyQueryBuilderTransformerInterface $currencyQbTransformer
     */
    public function __construct(
        AccountCustomerManager $accountCustomerManager,
        CurrencyQueryBuilderTransformerInterface $currencyQbTransformer
    ) {
        $this->accountCustomerManager = $accountCustomerManager;
        $this->currencyQbTransformer = $currencyQbTransformer;
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
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $customerTargetFields = $this->accountCustomerManager->getCustomerTargetFields();
        foreach ($this->getChangedEntities($uow) as $entity) {
            if ($entity instanceof Opportunity) {
                $this->scheduleOpportunityAccount($entity, $uow);
            } elseif ($entity instanceof Customer) {
                $this->scheduleCustomerAccounts($entity, $uow, $customerTargetFields);
            }
        }
    }

    /**
     * @param UnitOfWork $uow
     * @return \Generator
     */
    protected function getChangedEntities(UnitOfWork $uow)
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

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->accounts) {
            return;
        }

        $em = $args->getEntityManager();
        $lifetimeRepository = $em->getRepository('OroChannelBundle:LifetimeValueHistory');
        $noCustomerCondition = $this->createNoCustomerCondition('c');

        $historyUpdates = [];
        foreach ($this->accounts as $account) {
            $qb = $em->getRepository(Opportunity::class)
                ->createQueryBuilder('o');

            $closeRevenueSelect = $this->currencyQbTransformer->getTransformSelectQuery('closeRevenue', $qb, 'o');

            $qb
                ->select(sprintf('SUM(%s)', $closeRevenueSelect))
                ->join('o.customerAssociation', 'c')
                ->andWhere('c.account = :account')
                ->andWhere('o.status = :status')
                ->setParameters([
                    'account' => $account->getId(),
                    'status' => Opportunity::STATUS_WON,
                ]);

            if ($noCustomerCondition) {
                $qb->andWhere($noCustomerCondition);
            }

            $lifetimeAmount = (double) $qb
                ->getQuery()
                ->getSingleScalarResult();

            $history = new LifetimeValueHistory();
            $history->setAmount($lifetimeAmount);
            $history->setAccount($account);
            $em->persist($history);

            $historyUpdates[] = [$account, null, $history];
        }

        $this->accounts = [];
        $em->flush();
        $lifetimeRepository->massStatusUpdate($historyUpdates);
    }

    /**
     * @param string $customerAlias
     *
     * @return string
     */
    protected function createNoCustomerCondition($customerAlias)
    {
        $customerTargetFields = $this->accountCustomerManager->getCustomerTargetFields();

        return implode(
            ' AND ',
            array_map(
                function ($customerTargetField) use ($customerAlias) {
                    return sprintf('%s.%s IS NULL', $customerAlias, $customerTargetField);
                },
                $customerTargetFields
            )
        );
    }

    /**
     * @param Opportunity $entity
     * @param UnitOfWork $uow
     */
    protected function scheduleOpportunityAccount(Opportunity $entity, UnitOfWork $uow)
    {
        if (!$customerAssociation = $entity->getCustomerAssociation()) {
            return;
        }

        if (!($account = $customerAssociation->getTarget()) instanceof Account) {
            return;
        }

        $changeSet = $uow->getEntityChangeSet($entity);
        if ($uow->isScheduledForDelete($entity) ||
            (array_intersect(['closeRevenueValue', 'status', 'customerAssociation'], array_keys($changeSet)) &&
                (($entity->getStatus() && $entity->getStatus()->getId() === Opportunity::STATUS_WON) ||
                    !empty($changeSet['status'][0]) && $changeSet['status'][0]->getId() === Opportunity::STATUS_WON))
        ) {
            if (isset($changeSet['customerAssociation'])) {
                list($oldCustomer) = $changeSet['customerAssociation'];
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

    /**
     * @param Customer $entity
     * @param UnitOfWork $uow
     * @param array $customerTargetFields
     */
    protected function scheduleCustomerAccounts(Customer $entity, UnitOfWork $uow, array $customerTargetFields)
    {
        $changeSet = $uow->getEntityChangeSet($entity);
        if (isset($changeSet['account'])) {
            list($oldAccount, $newAccount) = $changeSet['account'];
            if ($oldAccount) {
                $this->scheduleAccount($oldAccount, $uow);
            }
            if ($newAccount && (!$oldAccount || $oldAccount->getId() !== $newAccount->getId())) {
                $this->scheduleAccount($newAccount, $uow);
            }
        } elseif (array_intersect($customerTargetFields, array_keys($changeSet))) {
            $account = $entity->getAccount();
            $this->scheduleAccount($account, $uow);
        }
    }

    /**
     * @param Account $account
     * @param UnitOfWork $uow
     */
    protected function scheduleAccount(Account $account, UnitOfWork $uow)
    {
        if ($uow->isScheduledForDelete($account)) {
            return;
        }

        $this->accounts[spl_object_hash($account)] = $account;
    }
}
