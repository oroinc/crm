<?php

namespace OroCRM\Bundle\MagentoBundle\Datagrid;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

abstract class AbstractTwoWaySyncActionPermissionProvider
{
    const CHANNEL_KEY = 'channelId';

    /**
     * @var array
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
     * @param ResultRecordInterface $record
     *
     * @return bool
     */
    protected function isTwoWaySyncEnable(ResultRecordInterface $record)
    {
        $channelId = $record->getValue(self::CHANNEL_KEY);
        if (!$channelId) {
            return false;
        }

        if (!empty($this->channels[$channelId])) {
            return $this->channels[$channelId];
        }

        /** @var Channel $channel */
        $channel = $this->doctrineHelper
            ->getEntityRepository($this->channelClassName)
            ->find($channelId);

        if (!$channel) {
            return false;
        }

        $isTwoWaySyncEnabled = $channel->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled');
        $this->channels[$channelId] = $isTwoWaySyncEnabled;

        return $isTwoWaySyncEnabled;
    }
}
