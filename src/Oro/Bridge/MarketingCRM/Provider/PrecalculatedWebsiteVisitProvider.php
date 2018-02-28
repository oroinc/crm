<?php

namespace Oro\Bridge\MarketingCRM\Provider;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\MagentoBundle\Provider\WebsiteVisitProviderInterface;

class PrecalculatedWebsiteVisitProvider extends AbstractPrecalculatedVisitProvider implements
    WebsiteVisitProviderInterface
{
    /**
     * @var WebsiteVisitProviderInterface
     */
    private $visitProvider;

    /**
     * @param WebsiteVisitProviderInterface $visitProvider
     */
    public function setVisitProvider(WebsiteVisitProviderInterface $visitProvider)
    {
        $this->visitProvider = $visitProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getSiteVisitsValues($dateRange)
    {
        if (!$this->isPrecalculatedStatisticEnabled()) {
            return $this->visitProvider->getSiteVisitsValues($dateRange);
        }

        if (!$this->isFeaturesEnabled()) {
            return 0;
        }

        $queryBuilder = $this->getVisitsCountQueryBuilder($dateRange['start'], $dateRange['end']);

        return $this->getSingleIntegerResult($queryBuilder);
    }

    /**
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     *
     * @return QueryBuilder
     */
    private function getVisitsCountQueryBuilder(\DateTime $from = null, \DateTime $to = null)
    {
        $queryBuilder = $this->createUniqueVisitQueryBuilder();

        $queryBuilder->select('SUM(t.visitCount)')
            ->join('t.trackingWebsite', 'site')
            ->leftJoin('site.channel', 'channel')
            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->isNull('channel.id'),
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('channel.channelType', ':channel'),
                    $queryBuilder->expr()->eq('channel.status', ':status')
                )
            ))
            /**
             * @todo Remove dependency on exact magento channel type in CRM-8153
             */
            ->setParameter('channel', MagentoChannelType::TYPE)
            ->setParameter('status', Channel::STATUS_ACTIVE);

        $this->applyDateLimit($queryBuilder, $from, $to);

        return $queryBuilder;
    }
}
