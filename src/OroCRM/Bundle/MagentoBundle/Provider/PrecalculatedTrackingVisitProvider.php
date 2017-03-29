<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\QueryBuilder;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class PrecalculatedTrackingVisitProvider extends TrackingVisitProvider
{
    use PrecalculatedVisitProviderTrait;

    /**
     * {@inheritdoc}
     */
    protected function getManagerRegistry()
    {
        return $this->registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAclHelper()
    {
        return $this->aclHelper;
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
            return parent::getDeeplyVisitedCount($from, $to);
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
            return parent::getVisitedCount($from, $to);
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
            ->setParameter('channel', ChannelType::TYPE)
            ->setParameter('status', Channel::STATUS_ACTIVE);

        return $queryBuilder;
    }
}
