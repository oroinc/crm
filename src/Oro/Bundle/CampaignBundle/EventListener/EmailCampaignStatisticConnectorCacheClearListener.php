<?php

namespace Oro\Bundle\CampaignBundle\EventListener;

use Doctrine\Common\EventSubscriber;

use Oro\Bundle\CampaignBundle\Model\EmailCampaignStatisticsConnector;

class EmailCampaignStatisticConnectorCacheClearListener implements EventSubscriber
{
    /**
     * @var EmailCampaignStatisticsConnector
     */
    protected $emailCampaignStatisticsConnector;

    /**
     * @param EmailCampaignStatisticsConnector $emailCampaignStatisticsConnector
     */
    public function __construct(EmailCampaignStatisticsConnector $emailCampaignStatisticsConnector)
    {
        $this->emailCampaignStatisticsConnector = $emailCampaignStatisticsConnector;
    }

    public function onClear()
    {
        $this->emailCampaignStatisticsConnector->clearMarketingListItemCache();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'onClear'
        ];
    }
}
