<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Analytics;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\AnalyticsBundle\Builder\RFMProviderInterface;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Model\CustomerIdentityInterface;

abstract class AbstractCustomerRFMProvider implements RFMProviderInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    protected $cache;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string $className
     */
    public function __construct(DoctrineHelper $doctrineHelper, $className)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity)
    {
        return $entity instanceof RFMAwareInterface
            && $entity instanceof CustomerIdentityInterface
            && $entity instanceof $this->className;
    }

    /**
     * @param RFMAwareInterface $entity
     *
     * {@inheritdoc}
     */
    public function getValue(RFMAwareInterface $entity)
    {
        $dataChannel = $entity->getDataChannel();
        $channelId = $dataChannel->getId();
        if (!isset($this->cache[$channelId])) {
            $this->cache[$channelId] = array_reduce($this->getValues($dataChannel), function ($result, array $sum) {
                $result[$sum['id']] = $sum['value'];
                return $result;
            }, []);
        }

        $id = $this->doctrineHelper->getSingleEntityIdentifier($entity);

        return isset($this->cache[$channelId][$id]) ? $this->cache[$channelId][$id] : null;
    }

    /**
     * @param Channel $dataChannel
     * @return mixed
     */
    abstract protected function getValues(Channel $dataChannel);
}
