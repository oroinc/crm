<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use DateTime;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class TrackingVisitProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param ManagerRegistry $registry
     * @param AclHelper       $aclHelper
     */
    public function __construct(ManagerRegistry $registry, AclHelper $aclHelper)
    {
        $this->registry  = $registry;
        $this->aclHelper = $aclHelper;
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
            $qb
                ->select('COUNT(DISTINCT t.userIdentifier)')
                ->join('t.trackingWebsite', 'tw')
                ->join('tw.channel', 'c')
                ->andWhere('c.channelType = :channel')
                ->andWhere($qb->expr()->eq('c.status', ':status'))
                ->andWhere($qb->expr()->between('t.firstActionTime', ':from', ':to'))
                ->setParameters([
                    'channel' => ChannelType::TYPE,
                    'from'    => $from,
                    'to'      => $to,
                    'status'  => Channel::STATUS_ACTIVE
                ])
                ->andHaving('COUNT(t.userIdentifier) > 1');

            return (int) $this->aclHelper->apply($qb)->getSingleScalarResult();
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
            $qb
                ->select('COUNT(DISTINCT t.userIdentifier)')
                ->join('t.trackingWebsite', 'tw')
                ->join('tw.channel', 'c')
                ->andWhere('c.channelType = :channel')
                ->andWhere($qb->expr()->eq('c.status', ':status'))
                ->andWhere($qb->expr()->between('t.firstActionTime', ':from', ':to'))
                ->setParameters([
                    'channel' => ChannelType::TYPE,
                    'from'    => $from,
                    'to'      => $to,
                    'status'  => Channel::STATUS_ACTIVE
                ]);

            return (int) $this->aclHelper->apply($qb)->getSingleScalarResult();
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
