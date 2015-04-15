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
        return $this->getChannelById($channelId)
            ->getSynchronizationSettings()
            ->offsetGetOr('isTwoWaySyncEnabled');
    }

    /**
     * @param mixed $channelId
     *
     * @return bool
     */
    public function isSupportedExtensionVersion($channelId)
    {
        /** @var MagentoSoapTransport $transport */
        $transport = $this->getChannelById($channelId)->getTransport();

        return $transport->isSupportedExtensionVersion();
    }

    /**
     * @param mixed $channelId
     *
     * @return bool
     */
    public function isEnabled($channelId)
    {
        return $this->getChannelById($channelId)->isEnabled();
    }

    /**
     * @param mixed $channelId
     * @param bool $checkExtension
     *
     * @return bool
     */
    public function isChannelApplicable($channelId, $checkExtension = true)
    {
        $isChannelApplicable = $this->isEnabled($channelId) && $this->isTwoWaySyncEnable($channelId);

        if ($checkExtension) {
            $isChannelApplicable = $isChannelApplicable && $this->isSupportedExtensionVersion($channelId);
        }

        return $isChannelApplicable;
    }

    /**
     * @param bool $checkExtension
     *
     * @return bool
     */
    public function hasApplicableChannels($checkExtension = true)
    {
        $isApplicable = false;

        /** @var Channel[] $channels */
        $channels = $this->doctrineHelper
            ->getEntityRepository($this->channelClassName)
            ->findBy(['type' => ChannelType::TYPE, 'enabled' => true]);

        if (!$channels) {
            return false;
        }

        foreach ($channels as $channel) {
            $channelId = $channel->getId();
            $this->channels[$channelId] = $channel;

            $isChannelApplicable = $this->isEnabled($channelId) && $this->isTwoWaySyncEnable($channelId);

            if ($checkExtension) {
                $isChannelApplicable = $isChannelApplicable && $this->isSupportedExtensionVersion($channelId);
            }

            $isApplicable = $isApplicable || $isChannelApplicable;
        }

        return $isApplicable;
    }

    /**
     * @param int $channelId
     * @return Channel
     */
    protected function getChannelById($channelId)
    {
        $this->validateChannelId($channelId);
        if (empty($this->channels[$channelId])) {
            /** @var Channel $channel */
            $channel = $this->doctrineHelper
                ->getEntityRepository($this->channelClassName)
                ->find($channelId);

            if (!$channel) {
                throw new \InvalidArgumentException(sprintf('Channel with id "%s" not found', $channelId));
            }

            $this->channels[$channelId] = $channel;
        }

        return $this->channels[$channelId];
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
