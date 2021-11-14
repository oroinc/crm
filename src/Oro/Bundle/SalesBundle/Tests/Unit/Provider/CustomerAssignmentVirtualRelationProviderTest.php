<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Provider\CustomerAssignmentVirtualRelationProvider;

class CustomerAssignmentVirtualRelationProviderTest extends \PHPUnit\Framework\TestCase
{
    private const CLASS_NAME = B2bCustomer::class;

    private array $config = [
        CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_RELATION_NAME => [
            'label' => 'oro.sales.opportunity.entity_label',
            'relation_type' => 'oneToMany',
            'related_entity_name' => Opportunity::class,
            'target_join_alias' => CustomerAssignmentVirtualRelationProvider::OPPORTUNITY_TARGET_ALIAS,
            'query' =>[
                'join' => [
                    'left' => [
                        [
                            'join' => Customer::class,
                            'alias' => 'virtualOpportunity_c',
                            'conditionType' => 'WITH',
                            'condition' => 'entity = virtualOpportunity_c.b2b_customer_188b774c'
                        ],
                        [
                            'join' => Opportunity::class,
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
            'related_entity_name' => Lead::class,
            'target_join_alias' => CustomerAssignmentVirtualRelationProvider::LEAD_TARGET_ALIAS,
            'query' =>[
                'join' => [
                    'left' => [
                        [
                            'join' => Customer::class,
                            'alias' => 'virtualLead_c',
                            'conditionType' => 'WITH',
                            'condition' => 'entity = virtualLead_c.b2b_customer_188b774c'
                        ],
                        [
                            'join' => Lead::class,
                            'alias' => 'virtualLead',
                            'conditionType' => 'WITH',
                            'condition' => 'virtualLead_c = virtualLead.customerAssociation'
                        ]
                    ]
                ]
            ]
        ]
    ];

    /** @var CustomerAssignmentVirtualRelationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new CustomerAssignmentVirtualRelationProvider(self::CLASS_NAME);
    }

    /**
     * @dataProvider isVirtualRelationDataProvider
     */
    public function testIsVirtualRelation(string $class, string $field, bool $expected)
    {
        $this->assertEquals($expected, $this->provider->isVirtualRelation($class, $field));
    }

    public function isVirtualRelationDataProvider(): array
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
     */
    public function testGetVirtualRelations(string $className, array $expected)
    {
        $this->assertEquals($expected, $this->provider->getVirtualRelations($className));
    }

    public function getVirtualRelationsDataProvider(): array
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
     */
    public function testGetVirtualRelationQuery(string $class, string $field, array $expected)
    {
        $this->assertEquals($expected, $this->provider->getVirtualRelationQuery($class, $field));
    }

    public function getVirtualRelationQueryDataProvider(): array
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
     */
    public function testGetTargetJoinAlias(string $class, string $field, string $expected)
    {
        $this->assertEquals($expected, $this->provider->getTargetJoinAlias($class, $field));
    }

    public function getTargetJoinAliasDataProvider(): array
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
