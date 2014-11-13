<?php

namespace OroCRM\Bundle\MarketingListBundle\Model;

use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem;

class MarketingListItemConnector
{
    const MARKETING_LIST_ITEM_ENTITY = 'OroCRMMarketingListBundle:MarketingListItem';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param ManagerRegistry $registry
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(ManagerRegistry $registry, DoctrineHelper $doctrineHelper)
    {
        $this->registry = $registry;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param MarketingList $marketingList
     * @param int $entityId
     * @return MarketingListItem
     */
    public function getMarketingListItem(MarketingList $marketingList, $entityId)
    {
        $marketingListItemRepository = $this->registry->getRepository(self::MARKETING_LIST_ITEM_ENTITY);
        $marketingListItem = $marketingListItemRepository->findOneBy(
            ['marketingList' => $marketingList, 'entityId' => $entityId]
        );

        if (!$marketingListItem) {
            $marketingListItem = new MarketingListItem();
            $marketingListItem->setMarketingList($marketingList)
                ->setEntityId($entityId);

            $manager = $this->registry->getManagerForClass(self::MARKETING_LIST_ITEM_ENTITY);
            $manager->persist($marketingListItem);
        }

        return $marketingListItem;
    }

    /**
     * @param MarketingList $marketingList
     * @param int $entityId
     * @return MarketingListItem
     */
    public function contact(MarketingList $marketingList, $entityId)
    {
        $marketingListItem = $this->getMarketingListItem($marketingList, $entityId);
        $marketingListItem->contact();

        return $marketingListItem;
    }

    /**
     * @param MarketingList $marketingList
     * @param array $result
     * @return MarketingListItem
     */
    public function contactResultRow(MarketingList $marketingList, array $result)
    {
        $idName = $this->doctrineHelper->getSingleEntityIdentifierFieldName($marketingList->getEntity());
        if (empty($result[$idName])) {
            throw new \InvalidArgumentException('Result row must contain identifier field');
        }

        return $this->contact($marketingList, $result[$idName]);
    }
}
