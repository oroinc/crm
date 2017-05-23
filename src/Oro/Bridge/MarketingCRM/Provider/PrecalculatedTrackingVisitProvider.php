<?php

namespace Oro\Bridge\MarketingCRM\Provider;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\MagentoBundle\Provider\TrackingVisitProviderInterface;

class PrecalculatedTrackingVisitProvider extends AbstractPrecalculatedVisitProvider implements
    TrackingVisitProviderInterface
{
    /**
     * @var TrackingVisitProviderInterface
     */
    private $trackingVisitProvider;

    /**
     * @param TrackingVisitProviderInterface $trackingVisitProvider
     */
    public function setVisitProvider(TrackingVisitProviderInterface $trackingVisitProvider)
    {
        $this->trackingVisitProvider = $trackingVisitProvider;
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
        return $this->trackingVisitProvider->getAggregates($customers);
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int
     */
    public function getDeeplyVisitedCount(\DateTime $from = null, \DateTime $to = null)
    {
        if (!$this->isPrecalculatedStatisticEnabled()) {
            return $this->trackingVisitProvider->getDeeplyVisitedCount($from, $to);
        }

        if (!$this->isFeaturesEnabled()) {
            return 0;
        }

        $queryBuilder = $this->getVisitCountQueryBuilder($from, $to);
        $queryBuilder->andHaving('COUNT(t.userIdentifier) > 1');

        return $this->getSingleIntegerResult($queryBuilder);
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int
     */
    public function getVisitedCount(\DateTime $from = null, \DateTime $to = null)
    {
        if (!$this->isPrecalculatedStatisticEnabled()) {
            return $this->trackingVisitProvider->getVisitedCount($from, $to);
        }

        if (!$this->isFeaturesEnabled()) {
            return 0;
        }

        $queryBuilder = $this->getVisitCountQueryBuilder($from, $to);

        return $this->getSingleIntegerResult($queryBuilder);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @return QueryBuilder
     */
    private function getVisitCountQueryBuilder(\DateTime $from = null, \DateTime $to = null)
    {
        $queryBuilder = $this->createUniqueVisitQueryBuilder();
        $this->applyDateLimit($queryBuilder, $from, $to);

        $queryBuilder
            ->select($queryBuilder->expr()->countDistinct('t.userIdentifier'))
            ->join('t.trackingWebsite', 'tw')
            ->join('tw.channel', 'c')
            ->andWhere($queryBuilder->expr()->eq('c.channelType', ':channel'))
            ->andWhere($queryBuilder->expr()->eq('c.status', ':status'))
            /**
             * @todo Remove dependency on exact magento channel type in CRM-8153
             */
            ->setParameter('channel', MagentoChannelType::TYPE)
            ->setParameter('status', Channel::STATUS_ACTIVE);

        return $queryBuilder;
    }
}
