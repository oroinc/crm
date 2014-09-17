<?php

namespace OroCRM\Bundle\ChannelBundle\Provider\Lifetime;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

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
     * @param Account      $account
     * @param Channel|null $channel
     *
     * @return double
     */
    public function getAccountLifeTimeValue(Account $account, Channel $channel = null)
    {
        if (null !== $channel) {
            $qb = $this->getChannelAccountLifetimeQueryBuilder();
            $qb->setParameter('dataChannel', $channel);
            $qb->setParameter('account', $account);

            $result = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_SCALAR);
        } else {
            $result = $this->getAccountLifetime($account->getId());
        }

        return (float)$result;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getChannelAccountLifetimeQueryBuilder()
    {
        /** @var EntityManager $em */
        $em = $this->registry->getManagerForClass('OroCRMChannelBundle:LifetimeValueHistory');
        $qb = $em->createQueryBuilder();
        $qb->from('OroCRMChannelBundle:LifetimeValueHistory', 'h');
        $qb->select('h.amount');
        $qb->andWhere('h.dataChannel = :dataChannel');
        $qb->andWhere('h.amount = :account');
        $qb->orderBy('h.id', 'DESC');
        $qb->setMaxResults(1);

        return $qb;
    }

    /**
     * @param int $accountId
     *
     * @return bool|string
     */
    protected function getAccountLifetime($accountId)
    {
        /** @var EntityManager $em */
        $em   = $this->registry->getManagerForClass('OroCRMChannelBundle:LifetimeValueHistory');
        $expr = $em->getExpressionBuilder();

        $qb = $em->createQueryBuilder();
        $qb->from('OroCRMChannelBundle:LifetimeValueHistory', 'h');
        $qb->select($expr->max('h.id'));
        $qb->where('h.account = :accountId');
        $qb->setParameter('accountId', $accountId);
        $qb->groupBy('h.dataChannel', 'h.account');

        /**
         * SELECT SUM(h.amount)
         * FROM (
         *     SELECT  MAX(dd.id) as id
         *     FROM history dd where account_id = 10
         *     GROUP BY  dd.c_id,  dd.account_id
         * ) as maxres
         * JOIN history h ON h.id = maxres.id WHERE h.account_id = 10
         */
        $statement = $em->getConnection()->executeQuery(
            'SELECT COUNT(*) FROM (' . $qb->getQuery()->getSQL() . ') AS e',
            [],
            []
        );
        return $statement->fetchColumn();
    }
}
