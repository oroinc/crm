<?php

namespace OroCRM\Bundle\CampaignBundle\Model;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListItemConnector;

class EmailCampaignStatisticsConnector
{
    /**
     * @var MarketingListItemConnector
     */
    protected $marketingListItemConnector;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @param MarketingListItemConnector $marketingListItemConnector
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        MarketingListItemConnector $marketingListItemConnector,
        DoctrineHelper $doctrineHelper
    ) {
        $this->marketingListItemConnector = $marketingListItemConnector;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
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

        $manager = $this->doctrineHelper->getEntityManager($this->entityName);
        $statisticsRecord = $manager->getRepository($this->entityName)
            ->findOneBy(['emailCampaign' => $emailCampaign, 'marketingListItem' => $marketingListItem]);

        if (!$statisticsRecord) {
            $statisticsRecord = new EmailCampaignStatistics();
            $statisticsRecord
                ->setEmailCampaign($emailCampaign)
                ->setMarketingListItem($marketingListItem)
                ->setOwner($emailCampaign->getOwner())
                ->setOrganization($emailCampaign->getOrganization());

            $manager->persist($statisticsRecord);
        }

        return $statisticsRecord;
    }
}
