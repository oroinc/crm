<?php

namespace Oro\Bundle\MagentoBundle\Model;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

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
        /** @var MagentoTransport $transport */
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
        $channels = $this->doctrineHelper
            ->getEntityRepository($this->channelClassName)
            ->findBy(['type' => MagentoChannelType::TYPE, 'enabled' => true]);

        return $this->collectionHasApplicableChannel($channels, $checkExtension);
    }

    /**
     * @param Organization $organization
     *
     * @param bool $checkExtension
     *
     * @return bool
     */
    public function hasOrganizationApplicableChannels(Organization $organization, $checkExtension = true)
    {
        /**
        * @todo Remove dependency on exact magento channel type in CRM-8153
        */
        $channels = $this->doctrineHelper
            ->getEntityRepository($this->channelClassName)
            ->findBy(['type' => MagentoChannelType::TYPE, 'enabled' => true, 'organization' => $organization]);

        return $this->collectionHasApplicableChannel($channels, $checkExtension);
    }

    /**
     * @param Channel[] $channels
     * @param bool $checkExtension
     * @return bool
     */
    protected function collectionHasApplicableChannel(array $channels, $checkExtension = true)
    {
        if (!$channels) {
            return false;
        }

        $isApplicable = false;

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
