<?php

namespace Oro\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\SalesBundle\Entity\Customer as CustomerAssociation;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

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
        if ($identityFQCN !== 'Oro\Bundle\CustomerBundle\Entity\Customer') {
            return 0.0;
        }

        $field = AccountCustomerManager::getCustomerTargetField($identityFQCN);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->from($identityFQCN, 'e');
        $qb->join(CustomerAssociation::class, 'ca', 'WITH', sprintf('ca.%s = e', $field));
        $qb->select(sprintf('SUM(e.%s)', $lifetimeField));
        $qb->andWhere('ca.account = :account');
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
     *      UPDATE OroChannelBundle:LifetimeValueHistory l SET status = :status
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
            $groupedByChannel[$channel ? $channel->getId() : ''][] = [$account, $excludeEntry];
        }

        foreach ($groupedByChannel as $channelId => $pairs) {
            $qb   = $this->getEntityManager()->createQueryBuilder();
            $expr = $qb->expr();

            $qb->update('OroChannelBundle:LifetimeValueHistory', 'l');
            $qb->set('l.status', ':status');
            $qb->setParameter('status', $qb->expr()->literal($status));

            if ($channelId !== '') {
                $qb
                    ->andWhere('l.dataChannel = :channel')
                    ->setParameter('channel', $channelId);
            } else {
                $qb->andWhere($qb->expr()->isNull('l.dataChannel'));
            }

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
