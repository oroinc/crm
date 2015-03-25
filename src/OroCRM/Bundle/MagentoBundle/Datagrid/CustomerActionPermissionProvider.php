<?php

namespace OroCRM\Bundle\MagentoBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class CustomerActionPermissionProvider
{
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
     * @param array $actions
     *
     * @return array
     */
    public function getCustomerActionsPermissions(ResultRecordInterface $record, array $actions)
    {
        $actions = array_keys($actions);
        $permissions = [];
        foreach ($actions as $action) {
            $permissions[$action] = true;
        }

        if (array_key_exists('update', $permissions)) {
            $permissions['update'] = $this->isTwoWaySyncEnable($record);
        }

        return $permissions;
    }

    /**
     * @param ResultRecordInterface $record
     *
     * @return bool
     */
    protected function isTwoWaySyncEnable(ResultRecordInterface $record)
    {
        $channelId = $record->getValue('channelId');
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
