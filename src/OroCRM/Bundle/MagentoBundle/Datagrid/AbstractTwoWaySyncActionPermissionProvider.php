<?php

namespace OroCRM\Bundle\MagentoBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

use OroCRM\Bundle\MagentoBundle\Model\ChannelSettingsProvider;

abstract class AbstractTwoWaySyncActionPermissionProvider
{
    const CHANNEL_KEY = 'channelId';
    const CUSTOMER_ID = 'customerId';
    const CUSTOMER    = 'customerData';
    const CUSTOMER_ORIGIN_ID = 'customerOriginId';
    /**
     * @var ChannelSettingsProvider
     */
    protected $channelSettingsProvider;

    /**
     * @param ChannelSettingsProvider $channelSettingsProvider
     */
    public function __construct(ChannelSettingsProvider $channelSettingsProvider)
    {
        $this->channelSettingsProvider = $channelSettingsProvider;
    }

    /**
     * @param ResultRecordInterface $record
     * @param bool $checkExtension
     *
     * @return bool
     */
    protected function isChannelApplicable(ResultRecordInterface $record, $checkExtension = true)
    {
        $channelId = $record->getValue(self::CHANNEL_KEY);

        return $this->channelSettingsProvider->isChannelApplicable($channelId, $checkExtension);
    }
}
