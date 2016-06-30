<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
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
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int
     */
    public function getDeeplyVisitedCount(\DateTime $from = null, \DateTime $to = null)
    {
        $qb = $this->getTrackingVisitRepository()->createQueryBuilder('t');

        try {
            $qb
                ->select('COUNT(DISTINCT t.userIdentifier)')
                ->join('t.trackingWebsite', 'tw')
                ->join('tw.channel', 'c')
                ->andWhere('c.channelType = :channel')
                ->andWhere($qb->expr()->eq('c.status', ':status'))
                ->setParameters([
                    'channel' => ChannelType::TYPE,
                    'status'  => Channel::STATUS_ACTIVE
                ])
                ->andHaving('COUNT(t.userIdentifier) > 1');
            if ($from) {
                $qb
                    ->andWhere('t.firstActionTime > :from')
                    ->setParameter('from', $from);
            }
            if ($to) {
                $qb
                    ->andWhere('t.firstActionTime < :to')
                    ->setParameter('to', $to);
            }

            return (int)$this->aclHelper->apply($qb)->getSingleScalarResult();
        } catch (NoResultException $ex) {
            return 0;
        }
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int
     */
    public function getVisitedCount(\DateTime $from = null, \DateTime $to = null)
    {
        $qb = $this->getTrackingVisitRepository()->createQueryBuilder('t');

        try {
            $qb
                ->select('COUNT(DISTINCT t.userIdentifier)')
                ->join('t.trackingWebsite', 'tw')
                ->join('tw.channel', 'c')
                ->andWhere('c.channelType = :channel')
                ->andWhere($qb->expr()->eq('c.status', ':status'))
                ->setParameters([
                    'channel' => ChannelType::TYPE,
                    'status'  => Channel::STATUS_ACTIVE
                ]);
            if ($from) {
                $qb
                    ->andWhere('t.firstActionTime > :from')
                    ->setParameter('from', $from);
            }
            if ($to) {
                $qb
                    ->andWhere('t.firstActionTime < :to')
                    ->setParameter('to', $to);
            }

            return (int)$this->aclHelper->apply($qb)->getSingleScalarResult();
        } catch (NoResultException $ex) {
            return 0;
        }
    }

    /**
     * @param string $alias
     *
     * @return QueryBuilder
     */
    public function getVisitedCountQB($alias)
    {
        $qb = $this->getTrackingVisitRepository()->createQueryBuilder($alias);
        $qb
            ->select(sprintf('COUNT(DISTINCT %s.userIdentifier)', $alias))
            ->join(sprintf('%s.trackingWebsite', $alias), 'tw')
            ->join('tw.channel', 'c')
            ->andWhere('c.channelType = :channel')
            ->andWhere($qb->expr()->eq('c.status', ':status'))
            ->setParameters([
                'channel' => ChannelType::TYPE,
                'status'  => Channel::STATUS_ACTIVE
            ]);

        return $qb;
    }

    /**
     * @param string $alias
     *
     * @return QueryBuilder
     */
    public function getDeeplyVisitedCountQB($alias)
    {
        $qb = $this->getTrackingVisitRepository()->createQueryBuilder($alias);
        $qb
            ->select(sprintf('COUNT(DISTINCT %s.userIdentifier)', $alias))
            ->join(sprintf('%s.trackingWebsite', $alias), 'tw')
            ->join('tw.channel', 'c')
            ->andWhere('c.channelType = :channel')
            ->andWhere($qb->expr()->eq('c.status', ':status'))
            ->setParameters([
                'channel' => ChannelType::TYPE,
                'status'  => Channel::STATUS_ACTIVE
            ])
            ->andHaving(sprintf('COUNT(%s.userIdentifier) > 1', $alias));

        return $qb;
    }

    /**
     * @return EntityRepository
     */
    protected function getTrackingVisitRepository()
    {
        return $this->registry->getRepository('OroTrackingBundle:TrackingVisit');
    }
}
