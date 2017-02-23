<?php

namespace Oro\Bundle\ChannelBundle\Provider\Lifetime;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;

class AmountProvider
{
    /** @var RegistryInterface */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Returns account lifetime value aggregated for all channels if $channel attribute is not passed.
     * Or for single channel otherwise.
     *
     * @param Account      $account
     * @param Channel|null $channel
     *
     * @return float
     */
    public function getAccountLifeTimeValue(Account $account, Channel $channel = null)
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
     * Returns query builder that allows to fetch account lifetime value from history table
     * Following parameters are required to be passed:
     *  - account  Account entity or identifier
     *
     * Following parameters are optional:
     *  - dataChannel - Channel entity or id to be used for fetch criteria, required if $addChannelParam is set to true
     *
     * @param bool $addChannelParam
     *
     * @return QueryBuilder
     */
    public function getChannelAccountLifetimeQueryBuilder($addChannelParam = false)
    {
        /** @var EntityManager $em */
        $em = $this->registry->getManagerForClass('OroChannelBundle:LifetimeValueHistory');
        $qb = $em->createQueryBuilder();
        $qb->from('OroChannelBundle:LifetimeValueHistory', 'h');
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
     * Returns query builder that allows to fetch list of lifetime values for each account
     *
     * @param null $ids     the identifiers of accounts the lifetimeValues for which is need to be fetched
     *
     * @return QueryBuilder
     */
    public function getAccountsLifetimeQueryBuilder($ids = null)
    {
        /** @var EntityManager $em */
        $em = $this->registry->getManagerForClass('OroChannelBundle:LifetimeValueHistory');
        $qb = $em->createQueryBuilder();
        $qb->select('IDENTITY(h.account) AS accountId, SUM(h.amount) AS lifetimeValue')
            ->from('OroChannelBundle:LifetimeValueHistory', 'h')
            ->leftJoin('h.dataChannel', 'ch')
            ->andWhere('h.status = :status')
            ->setParameter('status', $qb->expr()->literal(LifetimeValueHistory::STATUS_NEW))
            ->groupBy('h.account');

        if ($ids) {
            $qb->andWhere('IDENTITY(h.account) IN(:ids)')
                ->setParameter('ids', array_values($ids));
        }

        return $qb;
    }
}
