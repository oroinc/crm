<?php

namespace OroCRM\Bundle\CampaignBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListItemConnector;

class EmailCampaignStatisticsConnector
{
    const EMAIL_CAMPAIGN_STATISTICS_ENTITY = 'OroCRMCampaignBundle:EmailCampaignStatistics';

    /**
     * @var MarketingListItemConnector
     */
    protected $marketingListItemConnector;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    public function __construct(
        MarketingListItemConnector $marketingListItemConnector,
        ManagerRegistry $registry,
        DoctrineHelper $doctrineHelper
    ) {
        $this->marketingListItemConnector = $marketingListItemConnector;
        $this->registry = $registry;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @param object  $entity
     * @return EmailCampaignStatistics
     */
    public function getStatisticsRecord(EmailCampaign $emailCampaign, $entity)
    {
        $marketingList = $emailCampaign->getMarketingList();
        $entityId = $this->doctrineHelper->getSingleEntityIdentifier($entity);

        // Mark marketing list item as contacted
        $marketingListItem = $this->marketingListItemConnector
            ->getMarketingListItem($marketingList, $entityId);

        $statisticsRecord = $this->registry->getRepository(self::EMAIL_CAMPAIGN_STATISTICS_ENTITY)
            ->findOneBy(['emailCampaign' => $emailCampaign, 'marketingListItem' => $marketingListItem]);

        if (!$statisticsRecord) {
            $statisticsRecord = new EmailCampaignStatistics();
            $statisticsRecord
                ->setEmailCampaign($emailCampaign)
                ->setMarketingListItem($marketingListItem);

            $this->registry->getManagerForClass(self::EMAIL_CAMPAIGN_STATISTICS_ENTITY)
                ->persist($statisticsRecord);
        }

        return $statisticsRecord;
    }
}
