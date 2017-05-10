<?php

namespace Oro\Bridge\MarketingCRM\Entity\Repository;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepositoryAbstract;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class ChannelRepository extends ChannelRepositoryAbstract
{
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @return bool
     */
    private function isPrecalculatedStatisticEnabled()
    {
        return $this->configManager->get('oro_tracking.precalculated_statistic_enabled');
    }

    /**
     * @inheritdoc
     */
    public function getVisitsCountForChannelTypeQB($type)
    {
        $qb = $this->_em->createQueryBuilder();

        $entityName = $this->isPrecalculatedStatisticEnabled() ?
            'OroTrackingBundle:UniqueTrackingVisit' :
            'OroTrackingBundle:TrackingVisit';

        $qb->select('COUNT(visit.id)')
            ->from($entityName, 'visit')
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
