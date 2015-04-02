<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use DateTime;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Common\Persistence\ManagerRegistry;

use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class TrackingVisitProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return int
     */
    public function getDeeplyVisitedCount(DateTime $from, DateTime $to)
    {
        $qb = $this->getTrackingVisitRepository()->createQueryBuilder('t');

        try {
            return (int) $qb
                ->select('COUNT(DISTINCT t.userIdentifier)')
                ->join('t.trackingWebsite', 'tw')
                ->join('tw.channel', 'c')
                ->andWhere('c.channelType = :channel')
                ->andWhere($qb->expr()->between('t.lastActionTime', ':from', ':to'))
                ->setParameters([
                    'channel' => ChannelType::TYPE,
                    'from'    => $from,
                    'to'      => $to,
                ])
                ->groupBy('t.userIdentifier')
                ->andHaving('COUNT(t.userIdentifier) > 1')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        } catch (NoResultException $ex) {
            return 0;
        }
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return int
     */
    public function getVisitedCount(DateTime $from, DateTime $to)
    {
        $qb = $this->getTrackingVisitRepository()->createQueryBuilder('t');

        try {
            return (int) $qb
                ->select('COUNT(DISTINCT t.userIdentifier)')
                ->join('t.trackingWebsite', 'tw')
                ->join('tw.channel', 'c')
                ->andWhere('c.channelType = :channel')
                ->andWhere($qb->expr()->between('t.lastActionTime', ':from', ':to'))
                ->setParameters([
                    'channel' => ChannelType::TYPE,
                    'from'    => $from,
                    'to'      => $to,
                ])
                ->getQuery()
                ->getSingleScalarResult()
            ;
        } catch (NoResultException $ex) {
            return 0;
        }
    }

    /**
     * @return EntityRepository
     */
    protected function getTrackingVisitRepository()
    {
        return $this->registry->getRepository('OroTrackingBundle:TrackingVisit');
    }
}
