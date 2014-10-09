<?php

namespace OroCRM\Bundle\ChannelBundle\Provider\Lifetime;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;

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
        $em = $this->registry->getManagerForClass('OroCRMChannelBundle:LifetimeValueHistory');
        $qb = $em->createQueryBuilder();
        $qb->from('OroCRMChannelBundle:LifetimeValueHistory', 'h');
        $qb->select('SUM(h.amount)');
        $qb->andWhere('h.account = :account');
        if ($addChannelParam) {
            // do not change order, need for idx
            $qb->andWhere('h.dataChannel = :dataChannel');
        }
        $qb->leftJoin('h.dataChannel', 'ch');
        $qb->andWhere('ch.status = :channelStatus');
        $qb->setParameter('channelStatus', $qb->expr()->literal((int)Channel::STATUS_ACTIVE));
        $qb->andWhere('h.status = :status');
        $qb->setParameter('status', $qb->expr()->literal(LifetimeValueHistory::STATUS_NEW));
        $qb->setMaxResults(1);

        return $qb;
    }
}
