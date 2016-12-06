<?php

namespace Oro\Bridge\MarketingCRM\Entity\Repository;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepositoryAbstract;

class ChannelRepository extends ChannelRepositoryAbstract
{
    /**
     * @inheritdoc
     */
    public function getVisitsCountForChannelTypeQB($type)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('COUNT(visit.id)')
            ->from('OroTrackingBundle:TrackingVisit', 'visit')
            ->join('visit.trackingWebsite', 'site')
            ->leftJoin('site.channel', 'channel')
            ->where($qb->expr()->orX(
                $qb->expr()->isNull('channel.id'),
                $qb->expr()->andX(
                    $qb->expr()->eq('channel.channelType', ':type'),
                    $qb->expr()->eq('channel.status', ':status')
                )
            ))
            ->setParameter('type', $type)
            ->setParameter('status', Channel::STATUS_ACTIVE);

        return $qb;
    }
}
