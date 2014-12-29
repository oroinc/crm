<?php

namespace OroCRM\Bundle\MarketingListBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\EntityBundle\Provider\VirtualRelationProviderInterface;

class MarketingListItemVirtualRelationProvider implements VirtualRelationProviderInterface
{
    const FIELD_NAME = 'marketingListItem_virtual';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $marketingListByEntity = [];

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function isVirtualRelation($className, $fieldName)
    {
        return $this->hasMarketingList($className) && $fieldName === self::FIELD_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelationQuery($className, $fieldName)
    {
        $relationDefinition = $this->getRelationDefinition($className);
        return $relationDefinition['query'];
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelations($className)
    {
        if ($this->hasMarketingList($className)) {
            return [self::FIELD_NAME => $this->getRelationDefinition($className)];
        }

        return [];
    }

    /**
     * @param string $className
     * @return bool
     */
    protected function hasMarketingList($className)
    {
        if (!array_key_exists($className, $this->marketingListByEntity)) {
            $repository = $this->registry->getRepository('OroCRMMarketingListBundle:MarketingList');
            $this->marketingListByEntity[$className] = (bool)$repository->findOneBy(['entity' => $className]);
        }

        return $this->marketingListByEntity[$className];
    }

    /**
     * @param string $className
     * @return array
     */
    protected function getRelationDefinition($className)
    {
        return [
            'label' => 'orocrm.marketinglist.marketinglistitem.entity_label',
            'relation_type' => 'oneToMany',
            'related_entity_name' => 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem',
            'query' => [
                'join' => [
                    'left' => [
                        [
                            'join' => 'OroCRMMarketingListBundle:MarketingList',
                            'alias' => 'marketingList_virtual',
                            'conditionType' => 'WITH',
                            'condition' => 'marketingList_virtual.entity = ' . $className
                        ],
                        [
                            'join' => 'OroCRMMarketingListBundle:MarketingListItem',
                            'alias' => self::FIELD_NAME,
                            'conditionType' => 'WITH',
                            'condition' => self::FIELD_NAME . '.marketingList = marketingList_virtual'
                                . ' AND ' . self::FIELD_NAME . '.entityId = IDENTITY(entity)'
                        ]
                    ]
                ]
            ]
        ];
    }
}
