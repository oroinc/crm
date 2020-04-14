<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Provider\CustomerAssignmentVirtualRelationProvider;

class CustomerAssignmentVirtualRelationProviderTest extends \PHPUnit\Framework\TestCase
{
    const CLASS_NAME = 'Oro\Bundle\SalesBundle\Entity\B2bCustomer';

    /** @var CustomerAssignmentVirtualRelationProvider */
    protected $provider;

    /** @var array */
    protected $config = [
        CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME => [
            'label' => 'oro.sales.opportunity.entity_label',
            'relation_type' => 'oneToMany',
            'related_entity_name' => 'Oro\Bundle\SalesBundle\Entity\Opportunity',
            'target_join_alias' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_TARGET_ALIAS,
            'query' =>[
                'join' => [
                    'left' => [
                        [
                            'join' => 'Oro\Bundle\SalesBundle\Entity\Customer',
                            'alias' => 'virtualOpportunity_c',
                            'conditionType' => 'WITH',
                            'condition' => 'entity = virtualOpportunity_c.b2b_customer_188b774c'
                        ],
                        [
                            'join' => 'Oro\Bundle\SalesBundle\Entity\Opportunity',
                            'alias' => 'virtualOpportunity',
                            'conditionType' => 'WITH',
                            'condition' => 'virtualOpportunity_c = virtualOpportunity.customerAssociation'
                        ]
                    ]
                ]
            ]
        ],
        CustomerAssignmentVirtualRelationProvider::LEAD_RELATION_NAME => [
            'label' => 'oro.sales.lead.entity_label',
            'relation_type' => 'oneToMany',
            'related_entity_name' => 'Oro\Bundle\SalesBundle\Entity\Lead',
            'target_join_alias' => CustomerAssignmentVirtualRelationProvider::LEAD_TARGET_ALIAS,
            'query' =>[
                'join' => [
                    'left' => [
                        [
                            'join' => 'Oro\Bundle\SalesBundle\Entity\Customer',
                            'alias' => 'virtualLead_c',
                            'conditionType' => 'WITH',
                            'condition' => 'entity = virtualLead_c.b2b_customer_188b774c'
                        ],
                        [
                            'join' => 'Oro\Bundle\SalesBundle\Entity\Lead',
                            'alias' => 'virtualLead',
                            'conditionType' => 'WITH',
                            'condition' => 'virtualLead_c = virtualLead.customerAssociation'
                        ]
                    ]
                ]
            ]
        ]
    ];

    protected function setUp(): void
    {
        $this->provider = new CustomerAssignmentVirtualRelationProvider(self::CLASS_NAME);
    }

    /**
     * @dataProvider isVirtualRelationDataProvider
     *
     * @param string $class
     * @param string $field
     * @param bool $expected
     */
    public function testIsVirtualRelation($class, $field, $expected)
    {
        $this->assertEquals($expected, $this->provider->isVirtualRelation($class, $field));
    }

    /**
     * @return array
     */
    public function isVirtualRelationDataProvider()
    {
        return [
            'not supported class with Opportunity field' => [
                'class' => \stdClass::class,
                'field' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME,
                'expected' => false
            ],
            'not supported class with Lead field' => [
                'class' => \stdClass::class,
                'field' => CustomerAssignmentVirtualRelationProvider::LEAD_RELATION_NAME,
                'expected' => false
            ],
            'supported class with not supported field' => [
                'class' => B2bCustomer::class,
                'field' => 'testField',
                'expected' => false
            ],
            'supported class with Opportunity field' => [
                'class' => B2bCustomer::class,
                'field' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME,
                'expected' => true
            ],
            'supported class with Lead field' => [
                'class' => B2bCustomer::class,
                'field' => CustomerAssignmentVirtualRelationProvider::LEAD_RELATION_NAME,
                'expected' => true
            ],
        ];
    }

    /**
     * @dataProvider getVirtualRelationsDataProvider
     *
     * @param string $className
     * @param array $expected
     */
    public function testGetVirtualRelations($className, array $expected)
    {
        $this->assertEquals($expected, $this->provider->getVirtualRelations($className));
    }

    /**
     * @return array
     */
    public function getVirtualRelationsDataProvider()
    {
        return [
            'not supported class' => [
                'className' => \stdClass::class,
                'expected' => [],
            ],
            'supported class' => [
                'className' => B2bCustomer::class,
                'expected' => $this->config,
            ],
        ];
    }

    /**
     * @dataProvider getVirtualRelationQueryDataProvider
     *
     * @param string $class
     * @param string $field
     * @param array $expected
     */
    public function testGetVirtualRelationQuery($class, $field, array $expected)
    {
        $this->assertEquals($expected, $this->provider->getVirtualRelationQuery($class, $field));
    }

    /**
     * @return array
     */
    public function getVirtualRelationQueryDataProvider()
    {
        return [
            'not supported class with Opportunity field' => [
                'class' => \stdClass::class,
                'field' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME,
                'expected' => []
            ],
            'not supported class with Lead field' => [
                'class' => \stdClass::class,
                'field' => CustomerAssignmentVirtualRelationProvider::LEAD_RELATION_NAME,
                'expected' => []
            ],
            'supported class with not supported field' => [
                'class' => B2bCustomer::class,
                'field' => 'testField',
                'expected' => []
            ],
            'supported class with Opportunity field' => [
                'class' => B2bCustomer::class,
                'field' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME,
                'expected' =>
                    $this->config[CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME]['query']
            ],
            'supported class with Lead field' => [
                'class' => B2bCustomer::class,
                'field' => CustomerAssignmentVirtualRelationProvider::LEAD_RELATION_NAME,
                'expected' => $this->config[CustomerAssignmentVirtualRelationProvider::LEAD_RELATION_NAME]['query']
            ],
        ];
    }

    /**
     * @dataProvider getTargetJoinAliasDataProvider
     *
     * @param string $class
     * @param string $field
     * @param string $expected
     */
    public function testGetTargetJoinAlias($class, $field, $expected)
    {
        $this->assertEquals($expected, $this->provider->getTargetJoinAlias($class, $field));
    }

    /**
     * @return array
     */
    public function getTargetJoinAliasDataProvider()
    {
        return [
            'not supported class with Opportunity field' => [
                'class' => \stdClass::class,
                'field' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME,
                'expected' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME
            ],
            'not supported class with Lead field' => [
                'class' => \stdClass::class,
                'field' => CustomerAssignmentVirtualRelationProvider::LEAD_RELATION_NAME,
                'expected' => CustomerAssignmentVirtualRelationProvider::LEAD_RELATION_NAME
            ],
            'supported class with not supported field' => [
                'class' => B2bCustomer::class,
                'field' => 'testField',
                'expected' => 'testField'
            ],
            'supported class with Opportunity field' => [
                'class' => B2bCustomer::class,
                'field' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME,
                'expected' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_TARGET_ALIAS
            ],
            'supported class with Lead field' => [
                'class' => B2bCustomer::class,
                'field' => CustomerAssignmentVirtualRelationProvider::LEAD_RELATION_NAME,
                'expected' => CustomerAssignmentVirtualRelationProvider::LEAD_TARGET_ALIAS
            ],
        ];
    }
}
