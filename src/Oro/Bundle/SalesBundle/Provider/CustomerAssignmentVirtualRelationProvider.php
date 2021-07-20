<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\EntityBundle\Provider\VirtualRelationProviderInterface;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Provides virtual fields for an entity that is associated with Oro\Bundle\SalesBundle\Entity\Customer entity.
 */
class CustomerAssignmentVirtualRelationProvider implements VirtualRelationProviderInterface
{
    const OPPORTUNITY_RELATION_NAME = 'opportunities_virtual';
    const OPPORTUNITY_TARGET_ALIAS = 'virtualOpportunity';
    const LEAD_RELATION_NAME = 'leads_virtual';
    const LEAD_TARGET_ALIAS = 'virtualLead';

    /**
     * Class name which should has opportunity and load as relations.
     *
     * @var string
     */
    protected $sourceClass;

    public function __construct(string $sourceClass)
    {
        $this->sourceClass = $sourceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function isVirtualRelation($className, $fieldName)
    {
        return
            is_a($className, $this->sourceClass, true)
            && (
                self::OPPORTUNITY_RELATION_NAME === $fieldName
                || self::LEAD_RELATION_NAME === $fieldName
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelations($className)
    {
        if (!is_a($className, $this->sourceClass, true)) {
            return [];
        }

        return [
            self::OPPORTUNITY_RELATION_NAME => [
                'label' => 'oro.sales.opportunity.entity_label',
                'relation_type' => RelationType::ONE_TO_MANY,
                'related_entity_name' => Opportunity::class,
                'target_join_alias' => self::OPPORTUNITY_TARGET_ALIAS,
                'query' => $this->getQueryPart(Opportunity::class, self::OPPORTUNITY_TARGET_ALIAS)
            ],
            self::LEAD_RELATION_NAME => [
                'label' => 'oro.sales.lead.entity_label',
                'relation_type' => RelationType::ONE_TO_MANY,
                'related_entity_name' => Lead::class,
                'target_join_alias' => self::LEAD_TARGET_ALIAS,
                'query' => $this->getQueryPart(Lead::class, self::LEAD_TARGET_ALIAS)
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelationQuery($className, $fieldName)
    {
        $relations = $this->getVirtualRelations($className);

        return isset($relations[$fieldName]) ? $relations[$fieldName]['query'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetJoinAlias($className, $fieldName, $selectFieldName = null)
    {
        $relations = $this->getVirtualRelations($className);

        return isset($relations[$fieldName]) ? $relations[$fieldName]['target_join_alias'] : $fieldName;
    }

    /**
     * Returns query part that should be added to query for association target.
     *
     * @param string $targetClass
     * @param string $targetAlias
     * @return array
     */
    protected function getQueryPart($targetClass, $targetAlias)
    {
        $fieldName = AccountCustomerManager::getCustomerTargetField($this->sourceClass);
        $customerAlias = $targetAlias . '_c';

        return [
            'join' => [
                'left' => [
                    [
                        'join' => Customer::class,
                        'alias' => $customerAlias,
                        'conditionType' => Join::WITH,
                        'condition' => sprintf('entity = %s.%s', $customerAlias, $fieldName)
                    ],
                    [
                        'join' => $targetClass,
                        'alias' => $targetAlias,
                        'conditionType' => Join::WITH,
                        'condition' => sprintf('%s = %s.customerAssociation', $customerAlias, $targetAlias)
                    ],
                ]
            ]
        ];
    }
}
