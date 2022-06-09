<?php

namespace Oro\Bundle\ChannelBundle\Provider\Lifetime;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;

/**
 * Provides lifetime values for accounts.
 */
class AmountProvider
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Returns account lifetime value aggregated for all channels or for a single channel.
     */
    public function getAccountLifeTimeValue(Account $account, Channel $channel = null): float
    {
        if (null !== $channel) {
            $qb = $this->getChannelAccountLifetimeQueryBuilder(true);
            $qb->setParameter('dataChannel', $channel);
        } else {
            $qb = $this->getChannelAccountLifetimeQueryBuilder();
        }

        $qb->setParameter('account', $account);
        $result = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);

        return (float)($result ? reset($result) : 0);
    }

    /**
     * Returns query builder that allows to fetch account lifetime value from history table.
     * Following parameters are required to be passed:
     *  - account  Account entity or identifier
     *
     * Following parameters are optional:
     *  - dataChannel - Channel entity or id to be used for fetch criteria, required if $addChannelParam is set to true
     */
    public function getChannelAccountLifetimeQueryBuilder(bool $addChannelParam = false): QueryBuilder
    {
        /** @var EntityManagerInterface $em */
        $em = $this->doctrine->getManagerForClass(LifetimeValueHistory::class);
        $qb = $em->createQueryBuilder();
        $qb->from(LifetimeValueHistory::class, 'h');
        $qb->select('SUM(h.amount)');
        $qb->andWhere('h.account = :account');
        if ($addChannelParam) {
            // do not change order, need for idx
            $qb->andWhere('h.dataChannel = :dataChannel');
        }
        $qb->leftJoin('h.dataChannel', 'ch');
        $qb->andWhere('h.status = :status');
        $qb->setParameter('status', $qb->expr()->literal(LifetimeValueHistory::STATUS_NEW));
        $qb->setMaxResults(1);

        return $qb;
    }

    /**
     * Returns query builder that allows to fetch list of lifetime values for each account.
     */
    public function getAccountsLifetimeQueryBuilder(array $accountIds = null): QueryBuilder
    {
        /** @var EntityManagerInterface $em */
        $em = $this->doctrine->getManagerForClass(LifetimeValueHistory::class);
        $qb = $em->createQueryBuilder();
        $qb->select('IDENTITY(h.account) AS accountId, SUM(h.amount) AS lifetimeValue')
            ->from(LifetimeValueHistory::class, 'h')
            ->leftJoin('h.dataChannel', 'ch')
            ->andWhere('h.status = :status')
            ->setParameter('status', $qb->expr()->literal(LifetimeValueHistory::STATUS_NEW))
            ->groupBy('h.account');

        if ($accountIds) {
            $qb->andWhere('IDENTITY(h.account) IN(:ids)')
                ->setParameter('ids', array_values($accountIds));
        }

        return $qb;
    }
}
