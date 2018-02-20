<?php

namespace Oro\Bundle\AnalyticsBundle\Model;

use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class RFMMetricStateManager
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var string
     */
    private $interface;

    /**
     * @var string
     */
    private $channelClass;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string $interface
     * @param string $channelClass
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        $interface,
        $channelClass
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->interface    = $interface;
        $this->channelClass = $channelClass;
    }

    /**
     * @param Channel $channel
     */
    public function resetMetrics(Channel $channel = null)
    {
        $criteria = [];

        if ($channel) {
            $criteria = ['id' => $this->doctrineHelper->getSingleEntityIdentifier($channel)];
        }

        /** @var Channel[] $channels */
        $channels = $this->doctrineHelper->getEntityRepository($this->channelClass)->findBy($criteria);

        $channelsByCustomerIdentity = [];
        foreach ($channels as $channel) {
            $customerIdentity = $channel->getCustomerIdentity();

            if (!$customerIdentity) {
                continue;
            }

            if (!in_array($this->interface, class_implements($customerIdentity), true)) {
                continue;
            }

            $channelsByCustomerIdentity[$customerIdentity][] = $this->doctrineHelper
                ->getSingleEntityIdentifier($channel);
        }

        foreach ($channelsByCustomerIdentity as $className => $channelIds) {
            $this->executeResetQuery($className, $channelIds);
        }
    }

    /**
     * @param string $className
     * @param array  $ids
     */
    protected function executeResetQuery($className, $ids)
    {
        if (!$ids) {
            return;
        }

        $qb = $this->doctrineHelper
            ->getEntityManager($className)
            ->createQueryBuilder()
            ->update($className, 'e');

        foreach (RFMMetricCategory::$types as $type) {
            $qb
                ->set(sprintf('e.%s', $type), sprintf(':%s', $type))
                ->setParameter($type, null);
        }

        $qb
            ->where($qb->expr()->in('e.dataChannel', ':dataChannels'))
            ->setParameter('dataChannels', $ids);

        $qb->getQuery()->execute();
    }
}
