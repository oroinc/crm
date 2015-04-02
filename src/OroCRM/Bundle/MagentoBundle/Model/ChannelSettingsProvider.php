<?php

namespace OroCRM\Bundle\MagentoBundle\Model;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class ChannelSettingsProvider
{
    /**
     * @var Channel[]
     */
    protected $channels = [];

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $channelClassName;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param string $channelClassName
     */
    public function __construct(DoctrineHelper $doctrineHelper, $channelClassName)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->channelClassName = $channelClassName;
    }

    /**
     * @param mixed $channelId
     *
     * @return bool
     */
    public function isTwoWaySyncEnable($channelId)
    {
        $this->validateChannelId($channelId);

        if (!empty($this->channels[$channelId])) {
            return $this->channels[$channelId]->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled');
        }

        $this->loadChannel($channelId);

        return $this->channels[$channelId]->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled');
    }

    /**
     * @param mixed $channelId
     *
     * @return bool
     */
    public function isSupportedExtensionVersion($channelId)
    {
        $this->validateChannelId($channelId);

        if (!empty($this->channels[$channelId])) {
            /** @var MagentoSoapTransport $transport */
            $transport = $this->channels[$channelId]->getTransport();

            return $transport->isSupportedExtensionVersion();
        }

        $this->loadChannel($channelId);

        /** @var MagentoSoapTransport $transport */
        $transport = $this->channels[$channelId]->getTransport();

        return $transport->isSupportedExtensionVersion();
    }

    /**
     * @param mixed $channelId
     *
     * @return bool
     */
    public function isEnabled($channelId)
    {
        $this->validateChannelId($channelId);

        if (!empty($this->channels[$channelId])) {
            /** @var MagentoSoapTransport $transport */
            return $this->channels[$channelId]->isEnabled();
        }

        $this->loadChannel($channelId);

        return $this->channels[$channelId]->isEnabled();
    }

    /**
     * @param mixed $channelId
     *
     * @return bool
     */
    public function isChannelApplicable($channelId)
    {
        return $this->isEnabled($channelId)
            && $this->isTwoWaySyncEnable($channelId)
            && $this->isSupportedExtensionVersion($channelId);
    }

    /**
     * @return bool
     */
    public function hasApplicableChannels()
    {
        $isApplicable = false;

        /** @var Channel[] $channels */
        $channels = $this->doctrineHelper
            ->getEntityRepository($this->channelClassName)
            ->findBy(['type' => ChannelType::TYPE, 'enabled' => true]);

        if (!$channels) {
            return $isApplicable;
        }

        foreach ($channels as $channel) {
            $channelId = $channel->getId();
            $this->channels[$channelId] = $channel;

            $channelApplicable = $this->isEnabled($channelId)
                && $this->isTwoWaySyncEnable($channelId)
                && $this->isSupportedExtensionVersion($channelId);

            $isApplicable = $isApplicable || $channelApplicable;
        }

        return $isApplicable;
    }

    /**
     * @param $channelId
     */
    protected function loadChannel($channelId)
    {
        /** @var Channel $channel */
        $channel = $this->doctrineHelper
            ->getEntityRepository($this->channelClassName)
            ->find($channelId);

        if (!$channel) {
            throw new \InvalidArgumentException(sprintf('Channel with id "%s" not found', $channelId));
        }

        $this->channels[$channelId] = $channel;
    }

    /**
     * @param mixed $channelId
     */
    protected function validateChannelId($channelId)
    {
        $value = filter_var($channelId, FILTER_VALIDATE_INT);

        if (!is_int($value) || !$value) {
            throw new \InvalidArgumentException('Channel id value is wrong');
        }
    }
}
