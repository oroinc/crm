<?php

namespace OroCRM\Bundle\MarketingListBundle\Provider;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\VirtualRelationProviderInterface;

class MarketingListItemVirtualRelationProvider implements VirtualRelationProviderInterface
{
    const FIELD_NAME = 'marketingListItem_virtual';

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var array|null
     */
    protected $marketingListByEntity;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
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
        $relations = $this->getVirtualRelations($className);
        if (array_key_exists($fieldName, $relations)) {
            return $relations[$fieldName]['query'];
        }

        return [];
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
        if (null === $this->marketingListByEntity) {
            $this->marketingListByEntity = [];

            $repository = $this->doctrineHelper->getEntityRepository('OroCRMMarketingListBundle:MarketingList');
            $qb = $repository->createQueryBuilder('ml')
                ->select('ml.entity')
                ->distinct(true);
            $entities = $qb->getQuery()->getArrayResult();

            foreach ($entities as $entity) {
                $this->marketingListByEntity[$entity['entity']] = true;
            }
        }

        return !empty($this->marketingListByEntity[$className]);
    }

    /**
     * @param string $className
     * @return array
     */
    protected function getRelationDefinition($className)
    {
        $idField = $this->doctrineHelper->getSingleEntityIdentifierFieldName($className);

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
                            'conditionType' => Join::WITH,
                            'condition' => "marketingList_virtual.entity = '{$className}'"
                        ],
                        [
                            'join' => 'OroCRMMarketingListBundle:MarketingListItem',
                            'alias' => self::FIELD_NAME,
                            'conditionType' => Join::WITH,
                            'condition' => self::FIELD_NAME . '.marketingList = marketingList_virtual'
                                . ' AND entity.' . $idField . ' = ' . self::FIELD_NAME . '.entityId'
                        ]
                    ]
                ]
            ]
        ];
    }
}
