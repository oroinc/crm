<?php

namespace Oro\Bridge\MarketingCRM\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\MagentoBundle\Provider\TrackingVisitProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Calculates various tracking metrics.
 */
class TrackingVisitProvider implements TrackingVisitProviderInterface, FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

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
        if (!$this->isFeaturesEnabled()) {
            return null;
        }

        $queryBuilder = $this
            ->getTrackingVisitRepository()
            ->createQueryBuilder('t');

        try {
            $queryBuilder
                ->select('COUNT(DISTINCT t.userIdentifier)')
                ->join('t.trackingWebsite', 'tw')
                ->join('tw.channel', 'c')
                ->andWhere('c.channelType = :channel')
                ->andWhere($queryBuilder->expr()->eq('c.status', ':status'))
                /**
                 * Remove dependency on exact magento channel type in CRM-8153
                 */
                ->setParameters([
                    'channel' => MagentoChannelType::TYPE,
                    'status'  => Channel::STATUS_ACTIVE
                ])
                ->andHaving('COUNT(t.userIdentifier) > 1');
            if ($from) {
                $queryBuilder
                    ->andWhere('t.firstActionTime > :from')
                    ->setParameter('from', $from, Types::DATETIME_MUTABLE);
            }
            if ($to) {
                $queryBuilder
                    ->andWhere('t.firstActionTime < :to')
                    ->setParameter('to', $to, Types::DATETIME_MUTABLE);
            }

            return (int) $this->aclHelper->apply($queryBuilder)->getSingleScalarResult();
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
        if (!$this->isFeaturesEnabled()) {
            return null;
        }

        $queryBuilder = $this
            ->getTrackingVisitRepository()
            ->createQueryBuilder('t');

        try {
            $queryBuilder
                ->select('COUNT(DISTINCT t.userIdentifier)')
                ->join('t.trackingWebsite', 'tw')
                ->join('tw.channel', 'c')
                ->andWhere('c.channelType = :channel')
                ->andWhere($queryBuilder->expr()->eq('c.status', ':status'))
                /**
                 * Remove dependency on exact magento channel type in CRM-8153
                 */
                ->setParameters([
                    'channel' => MagentoChannelType::TYPE,
                    'status'  => Channel::STATUS_ACTIVE
                ]);
            if ($from) {
                $queryBuilder
                    ->andWhere('t.firstActionTime > :from')
                    ->setParameter('from', $from, Types::DATETIME_MUTABLE);
            }
            if ($to) {
                $queryBuilder
                    ->andWhere('t.firstActionTime < :to')
                    ->setParameter('to', $to, Types::DATETIME_MUTABLE);
            }

            return (int) $this->aclHelper->apply($queryBuilder)->getSingleScalarResult();
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
        if (!$this->isFeaturesEnabled()) {
            return [
                'count' => null,
                'last' => null,
                'monthly' => null,
            ];
        }

        $customerAssocName = ExtendHelper::buildAssociationName(Customer::class, 'identifier');

        $result = $this->getTrackingVisitRepository()
            ->createQueryBuilder('t')
            ->select('COUNT(DISTINCT t.userIdentifier) cnt')
            ->addSelect('MIN(t.firstActionTime) first')
            ->addSelect('MAX(t.firstActionTime) last')
            ->andWhere(sprintf('t.%s in (:customers)', $customerAssocName))
            ->andWhere('t.userIdentifier not like :guestId')
            ->setParameter('customers', $customers)
            ->setParameter('guestId', 'id=guest%')
            ->getQuery()
            ->getSingleResult();

        $count = (int) $result['cnt'];
        $first = new \DateTimeImmutable($result['first']);
        $last = new \DateTimeImmutable($result['last']);
        $daysDiff = $last->diff($first)->d;

        return [
            'count' => $count,
            'last' => $count > 0 ? $result['last'] : null,
            'monthly' => $daysDiff > 0 ? round($count / max($daysDiff / 30.42, 1)) : $count, // 30.42 = 365/12
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
