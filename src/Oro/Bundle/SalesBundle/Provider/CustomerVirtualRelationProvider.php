<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\EntityBundle\Provider\VirtualRelationProviderInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

/**
 * Adds the association target relations from Oro\Bundle\SalesBundle\Entity\Customer entity
 * to the given entity relations.
 */
class CustomerVirtualRelationProvider implements VirtualRelationProviderInterface
{
    /** @var AssociationManager */
    protected $associationManager;

    /** @var ConfigProvider */
    protected $configProvider;

    /**
     * Class name that has link to the customer entity which association targets should be added to relations.
     *
     * @var string
     */
    protected $sourceClass;

    public function __construct(AssociationManager $associationManager, ConfigProvider $configProvider)
    {
        $this->associationManager = $associationManager;
        $this->configProvider = $configProvider;
    }

    /**
     * Sets the class name that has link to the customer entity which association targets should be added to relations.
     *
     * @param string $className
     */
    public function setSourceClass($className)
    {
        $this->sourceClass = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function isVirtualRelation($className, $fieldName)
    {
        return
            is_a($className, $this->sourceClass, true)
            && in_array($fieldName, array_values($this->getTargets()), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelationQuery($className, $fieldName)
    {
        return $this->getQueryPart($fieldName);
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelations($className)
    {
        if (!is_a($className, $this->sourceClass, true)) {
            return [];
        }

        $targets = $this->getTargets();
        $result = [];
        foreach ($targets as $targetClassName => $fieldName) {
            $result[$fieldName] = [
                'label' => $this->configProvider->getConfig($targetClassName)->get('label'),
                'relation_type' => RelationType::MANY_TO_ONE,
                'related_entity_name' => $targetClassName,
                'target_join_alias' => $fieldName,
                'query' => $this->getQueryPart($fieldName)
            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetJoinAlias($className, $fieldName, $selectFieldName = null)
    {
        return $fieldName;
    }

    /**
     * Returns query part that should be added to query for association target.
     *
     * @param string $fieldName
     *
     * @return array
     */
    protected function getQueryPart($fieldName)
    {
        $customerAssociationAlias = $fieldName . '_ca';

        return [
            'join' => [
                'left' => [
                    [
                        'join' => 'entity.customerAssociation',
                        'alias' => $customerAssociationAlias,
                        'conditionType' => Join::WITH
                    ],
                    [
                        'join' => $customerAssociationAlias . '.' . $fieldName,
                        'alias' => $fieldName,
                        'conditionType' => Join::WITH
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns array with association target class names and field names of Customer entity.
     *
     * @return array ['className' => 'fieldName', ...]
     */
    protected function getTargets()
    {
        return $this->associationManager->getAssociationTargets(
            Customer::class,
            null,
            RelationType::MANY_TO_ONE,
            CustomerScope::ASSOCIATION_KIND
        );
    }
}
