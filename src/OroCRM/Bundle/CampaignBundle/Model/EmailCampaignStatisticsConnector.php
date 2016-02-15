<?php

namespace OroCRM\Bundle\CampaignBundle\Model;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem;
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
     * @var MarketingListItem[]
     */
    protected $marketingListItemCache = [];

    /**
     * @var EmailCampaignStatistics[]
     */
    protected $statisticRecordsCache = [];

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

        /**
         * Cache was added because there is a case:
         * - 2 email campaigns linked to one marketing list
         * - statistic can created using batches (marketing list item connector will be used)
         *  and flush will be run once per N records creation
         * in this case same Marketing list entity will be received twice for one marketing list
         * and new MarketingListItem for same $marketingList and $entityId will be persisted twice.
         *
         * Marketing list name used as key for cache because Id can be empty and name is unique
         *
         */
        if (empty($this->marketingListItemCache[$marketingList->getName()][$entityId])) {
            // Mark marketing list item as contacted
            $this->marketingListItemCache[$marketingList->getName()][$entityId] = $this->marketingListItemConnector
                ->getMarketingListItem($marketingList, $entityId);
        }
        /** @var MarketingListItem $marketingListItem */
        $marketingListItem = $this->marketingListItemCache[$marketingList->getName()][$entityId];
        $marketingListItemHash = spl_object_hash($marketingListItem);

        $manager = $this->doctrineHelper->getEntityManager($this->entityName);

        $statisticsRecord = null;
        if ($marketingListItem->getId() !== null) {
            $statisticsRecord = $manager->getRepository($this->entityName)
                ->findOneBy(['emailCampaign' => $emailCampaign, 'marketingListItem' => $marketingListItem]);
        } elseif (!empty($this->statisticRecordsCache[$emailCampaign->getId()][$marketingListItemHash])) {
            $statisticsRecord = $this->statisticRecordsCache[$emailCampaign->getId()][$marketingListItemHash];
        }

        if (!$statisticsRecord) {
            $statisticsRecord = new EmailCampaignStatistics();
            $statisticsRecord
                ->setEmailCampaign($emailCampaign)
                ->setMarketingListItem($marketingListItem)
                ->setOwner($emailCampaign->getOwner())
                ->setOrganization($emailCampaign->getOrganization());

            $this->statisticRecordsCache[$emailCampaign->getId()][$marketingListItemHash] = $statisticsRecord;
            $manager->persist($statisticsRecord);
        }

        return $statisticsRecord;
    }

    /**
     * Method must be called on onClear Doctrine event, because after clear entity manager
     * cached entities will be detached
     */
    public function clearMarketingListItemCache()
    {
        $this->marketingListItemCache = [];
        $this->statisticRecordsCache = [];
    }
}
