<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;

class LifetimeHistoryRepository extends EntityRepository
{
    /**
     * Calculates lifetime value based on "customer identity" values, limited by channel if given
     *
     * @param string  $identityFQCN
     * @param string  $lifetimeField
     * @param Account $account
     * @param Channel $channel
     *
     * @return double
     */
    public function calculateAccountLifetime($identityFQCN, $lifetimeField, Account $account, Channel $channel = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->from($identityFQCN, 'e');
        $qb->select(sprintf('SUM(e.%s)', $lifetimeField));
        $qb->andWhere('e.account = :account');
        $qb->setParameter('account', $account);

        if (null !== $channel) {
            $qb->andWhere('e.dataChannel = :channel');
            $qb->setParameter('channel', $channel);
        }

        return (float)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Update status of history entries based on data given
     * Generates following DQL for each channel passed:
     *      UPDATE OroCRMChannelBundle:LifetimeValueHistory l SET status = :status
     *      WHERE l.dataChannel = :dataChannel
     *              AND (
     *                  (l.id <> :exclusionEntityId1 and l.account = :account1)
     *                  OR
     *                  (l.id <> :exclusionEntityId1 and l.account = :account1)
     *              )
     *
     * @param array $records    Array of entities that will be used as limitation criteria
     *                          Should contain arrays with following data inside:
     *                          [0 => "account entity or ID", 1 => "channel entity or ID", "exclusion entity or ID"]
     * @param int   $status     Status to be set
     */
    public function massStatusUpdate(array $records, $status = LifetimeValueHistory::STATUS_OLD)
    {
        $groupedByChannel = [];
        /** @var Channel $channel */
        foreach ($records as $row) {
            list($account, $channel, $excludeEntry) = $row;
            $groupedByChannel[$channel->getId()][] = [$account, $excludeEntry];
        }

        foreach ($groupedByChannel as $channelId => $pairs) {
            $qb   = $this->getEntityManager()->createQueryBuilder();
            $expr = $qb->expr();

            $qb->update('OroCRMChannelBundle:LifetimeValueHistory', 'l');
            $qb->set('l.status', ':status');
            $qb->setParameter('status', $qb->expr()->literal($status));
            $qb->andWhere('l.dataChannel = :channel');
            $qb->setParameter('channel', $channelId);

            $criteria = [];
            foreach ($pairs as $k => $pair) {
                list($account, $excludeEntry) = $pair;

                $criteria[] = $expr->andX('l <> :eid' . $k, 'l.account = :aid' . $k);
                $qb->setParameter('eid' . $k, $excludeEntry);
                $qb->setParameter('aid' . $k, $account);
            }
            $qb->andWhere(call_user_func_array([$expr, 'orX'], $criteria));
            $qb->getQuery()->execute();
        }
    }
}
