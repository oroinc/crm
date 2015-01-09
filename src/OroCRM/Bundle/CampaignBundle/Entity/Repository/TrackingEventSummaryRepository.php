<?php

namespace OroCRM\Bundle\CampaignBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

class TrackingEventSummaryRepository extends EntityRepository
{
    /**
     * @param Campaign $campaign
     * @return QueryBuilder
     */
    public function getSummarizedStatistic(Campaign $campaign)
    {
        $today = new \DateTime('now', new \DateTimeZone('UTC'));

        $qb = $this->_em->createQueryBuilder()
            ->from('OroTrackingBundle:TrackingEvent', 'trackingEvent')
            ->select(
                [
                    'trackingEvent.name',
                    'IDENTITY(trackingEvent.website) as websiteId',
                    'COUNT(trackingEvent.id) as visitCount',
                    'DATE(trackingEvent.loggedAt) as loggedAtDate',
                ]
            )
            ->andWhere('trackingEvent.code = :trackingEventCode')
            ->andWhere('DATE(trackingEvent.loggedAt) != :today')
            ->setParameter('trackingEventCode', $campaign->getCode())
            ->setParameter('today', $today)
            ->groupBy('trackingEvent.name, trackingEvent.website, loggedAtDate');

        if ($campaign->getReportRefreshDate()) {
            $qb->andWhere('DATE(trackingEvent.loggedAt) > DATE(:since)')
                ->setParameter('since', $campaign->getReportRefreshDate());
        }

        return $qb->getQuery()->getArrayResult();
    }
}
