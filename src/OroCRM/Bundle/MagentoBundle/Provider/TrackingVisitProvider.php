<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

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
     * Return total number of visits, last visit date and visits per month
     * filtered by customers
     *
     * @param Customer[] $customers
     *
     * @return array
     */
    public function getAggregates(array $customers)
    {
        $customerAssocName = ExtendHelper::buildAssociationName(Customer::class, 'identifier');

        $result = $this->getTrackingVisitRepository()
            ->createQueryBuilder('t')
            ->select('COUNT(DISTINCT t.userIdentifier) cnt')
            ->addSelect('MIN(t.firstActionTime) first')
            ->addSelect('MAX(t.firstActionTime) last')
            ->andWhere(sprintf('t.%s in (:customers)', $customerAssocName))
            ->setParameter('customers', $customers)
            ->getQuery()
            ->getSingleResult();

        $count = (int) $result['cnt'];
        $first = new \DateTimeImmutable($result['first']);
        $last = new \DateTimeImmutable($result['last']);
        $monthsDiff = $last->diff($first)->m;

        return [
            'count' => $count,
            'last' => $count > 0 ? $result['last'] : null,
            'monthly' => $monthsDiff > 0 ? $count / $monthsDiff : $count,
        ];
    }

    /**
     * @return EntityRepository
     */
    protected function getTrackingVisitRepository()
    {
        return $this->registry->getRepository('OroTrackingBundle:TrackingVisit');
    }
}
