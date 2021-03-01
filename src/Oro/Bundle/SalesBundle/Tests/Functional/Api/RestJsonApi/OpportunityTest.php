<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures\LoadOpportunitiesData;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class OpportunityTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadOpportunitiesData::class]);
    }

    /**
     * @dataProvider cgetDataProvider
     */
    public function testCget(array $parameters, string $expectedDataFileName)
    {
        $response = $this->cget(['entity' => 'opportunities'], $parameters);

        $this->assertResponseContains($expectedDataFileName, $response);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function cgetDataProvider(): array
    {
        return [
            'without parameters'                                                 => [
                'parameters'      => [],
                'expectedContent' => 'cget_opportunity.yml'
            ],
            'filter by status'                                                   => [
                'parameters'      => [
                    'filter' => ['status' => 'won']
                ],
                'expectedContent' => 'cget_opportunity_filter_by_status.yml'
            ],
            'title without fields and include filters'                           => [
                'parameters'      => [
                    'meta' => 'title'
                ],
                'expectedContent' => 'cget_opportunity_title.yml'
            ],
            'fields and include filters for customer association'                => [
                'parameters'      => [
                    'fields[opportunities]' => 'account,customer',
                    'include'               => 'account,customer'
                ],
                'expectedContent' => 'cget_opportunity_customer_association.yml'
            ],
            'title for customer association'                                     => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'account,customer',
                    'include'               => 'account,customer'
                ],
                'expectedContent' => 'cget_opportunity_customer_association_title.yml'
            ],
            'fields and include filters for nested customer association'         => [
                'parameters'      => [
                    'fields[opportunities]' => 'lead,account,customer',
                    'fields[leads]'         => 'account,customer',
                    'include'               => 'lead.account,lead.customer'
                ],
                'expectedContent' => 'cget_opportunity_customer_association_nested.yml'
            ],
            'title for nested customer association'                              => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'lead',
                    'fields[leads]'         => 'account,customer',
                    'include'               => 'lead.account,lead.customer'
                ],
                'expectedContent' => 'cget_opportunity_customer_association_nested_title.yml'
            ],
            'fields and include filters for nested association of lead.account'  => [
                'parameters'      => [
                    'fields[opportunities]' => 'lead,account,customer',
                    'fields[leads]'         => 'account',
                    'fields[accounts]'      => 'name,organization',
                    'fields[organizations]' => 'name,users',
                    'include'               => 'lead.account.organization'
                ],
                'expectedContent' => 'cget_opportunity_customer_association_nested1.yml'
            ],
            'title for nested association of lead.account'                       => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'lead,account,customer',
                    'fields[leads]'         => 'account',
                    'fields[accounts]'      => 'name,organization',
                    'fields[organizations]' => 'name,users',
                    'include'               => 'lead.account.organization'
                ],
                'expectedContent' => 'cget_opportunity_customer_association_nested1_title.yml'
            ],
            'fields and include filters for nested association of lead.customer' => [
                'parameters'      => [
                    'fields[opportunities]' => 'lead,account,customer',
                    'fields[leads]'         => 'customer',
                    'fields[b2bcustomers]'  => 'name,organization',
                    'fields[organizations]' => 'name,users',
                    'include'               => 'lead.customer.organization'
                ],
                'expectedContent' => 'cget_opportunity_customer_association_nested2.yml'
            ],
            'title for nested association of lead.customer'                      => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'lead,account,customer',
                    'fields[leads]'         => 'customer',
                    'fields[b2bcustomers]'  => 'name,organization',
                    'fields[organizations]' => 'name,users',
                    'include'               => 'lead.customer.organization'
                ],
                'expectedContent' => 'cget_opportunity_customer_association_nested2_title.yml'
            ],
            'title for close reason and status'                                  => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'closeReason,status',
                    'include'               => 'closeReason,status'
                ],
                'expectedContent' => 'cget_opportunity_dictionary_title.yml'
            ]
        ];
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>']
        );

        $this->assertResponseContains('get_opportunity.yml', $response);
    }

    public function testCreate()
    {
        $response = $this->post(
            ['entity' => 'opportunities'],
            'create_opportunity.yml'
        );

        $this->assertResponseContains('create_opportunity.yml', $response);

        // test that the entity was created
        $entity = $this->getEntityManager()->find(Opportunity::class, $this->getResourceId($response));
        self::assertNotNull($entity);
    }

    public function testCreateWithNullCreatedAtAndUpdatedAt()
    {
        $data = $this->getRequestData('create_opportunity_min.yml');
        $data['data']['attributes']['createdAt'] = null;
        $data['data']['attributes']['updatedAt'] = null;
        $response = $this->post(['entity' => 'opportunities'], $data);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity = $this->getEntityManager()->find(Opportunity::class, $this->getResourceId($response));
        self::assertTrue($entity->getCreatedAt() !== null && $entity->getCreatedAt() <= $now);
        self::assertTrue($entity->getUpdatedAt() !== null && $entity->getUpdatedAt() <= $now);
    }

    public function testTryToCreateWithNegativeProbability()
    {
        $data = $this->getRequestData('create_opportunity_min.yml');
        $data['data']['attributes']['probability'] = -0.01;
        $response = $this->post(['entity' => 'opportunities'], $data, [], false);

        $this->assertResponseValidationError(
            [
                'title'  => 'range constraint',
                'detail' => 'This value should be between 0% and 100%.',
                'source' => ['pointer' => '/data/attributes/probability']
            ],
            $response
        );
    }

    public function testTryToCreateWithProbabilityGreaterThan100Percent()
    {
        $data = $this->getRequestData('create_opportunity_min.yml');
        $data['data']['attributes']['probability'] = 1.01;
        $response = $this->post(['entity' => 'opportunities'], $data, [], false);

        $this->assertResponseValidationError(
            [
                'title'  => 'range constraint',
                'detail' => 'This value should be between 0% and 100%.',
                'source' => ['pointer' => '/data/attributes/probability']
            ],
            $response
        );
    }

    public function testTryToCreateWithoutAccountAndCustomer()
    {
        $response = $this->post(
            ['entity' => 'opportunities'],
            $this->getRequestData('create_opportunity_no_account_and_customer.yml'),
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'Either an account or a customer should be set.'
            ],
            $response
        );
    }

    public function testTryToCreateWithInconsistentCustomer()
    {
        $response = $this->post(
            ['entity' => 'opportunities'],
            $this->getRequestData('create_opportunity_inconsistent_customer.yml'),
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'The customer should be a part of the specified account.',
                'source' => ['pointer' => '/data/relationships/customer/data']
            ],
            $response
        );
    }

    public function testCreateWithConsistentCustomer()
    {
        $response = $this->post(
            ['entity' => 'opportunities'],
            'create_opportunity_consistent_customer.yml'
        );

        $this->assertResponseContains('create_opportunity_consistent_customer.yml', $response);
    }

    public function testUpdateWithNullCreatedAtAndUpdatedAt()
    {
        /** @var Opportunity $opportunity */
        $opportunity = $this->getReference('opportunity1');
        $opportunityId = $opportunity->getId();
        $createdAt = $opportunity->getCreatedAt();
        $updatedAt = $opportunity->getUpdatedAt();
        $data = [
            'data' => [
                'type'       => 'opportunities',
                'id'         => (string)$opportunityId,
                'attributes' => [
                    'createdAt' => null,
                    'updatedAt' => null
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'opportunities', 'id' => (string)$opportunityId],
            $data
        );

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        self::assertEquals($createdAt, $entity->getCreatedAt());
        self::assertTrue($entity->getUpdatedAt() >= $updatedAt && $entity->getUpdatedAt() <= $now);
    }

    public function testUpdateShouldIgnoreCreatedAtAndUpdatedAt()
    {
        /** @var Opportunity $opportunity */
        $opportunity = $this->getReference('opportunity1');
        $opportunityId = $opportunity->getId();
        $createdAt = $opportunity->getCreatedAt();
        $updatedAt = $opportunity->getUpdatedAt();
        $data = [
            'data' => [
                'type'       => 'opportunities',
                'id'         => (string)$opportunityId,
                'attributes' => [
                    'createdAt' => '2019-01-01T01:01:01Z',
                    'updatedAt' => '2019-01-01T01:01:01Z'
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'opportunities', 'id' => (string)$opportunityId],
            $data
        );

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        self::assertEquals($createdAt, $entity->getCreatedAt());
        self::assertTrue($entity->getUpdatedAt() >= $updatedAt && $entity->getUpdatedAt() <= $now);
    }

    public function testGetLeadRelationship()
    {
        $response = $this->getRelationship(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'lead']
        );

        $this->assertResponseContains('get_relationship_opportunity_lead.yml', $response);
    }

    public function testGetLeadSubresource()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'lead']
        );

        $this->assertResponseContains('get_subresource_opportunity_lead.yml', $response);
    }

    public function testGetAccountRelationship()
    {
        $response = $this->getRelationship(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'account']
        );

        $this->assertResponseContains('get_relationship_opportunity_account.yml', $response);
    }

    public function testGetAccountSubresource()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'account']
        );

        $this->assertResponseContains('get_subresource_opportunity_account.yml', $response);
    }

    public function testGetAccountSubresourceWithIncludeFilter()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'account'],
            [
                'fields[accounts]' => 'name,organization',
                'include'          => 'organization'
            ]
        );

        $this->assertResponseContains('get_subresource_opportunity_account_include.yml', $response);
    }

    public function testGetAccountSubresourceWithTitle()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'account'],
            ['meta' => 'title']
        );

        $this->assertResponseContains('get_subresource_opportunity_account_title.yml', $response);
    }

    public function testGetCustomerRelationship()
    {
        $response = $this->getRelationship(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'customer']
        );

        $this->assertResponseContains('get_relationship_opportunity_customer.yml', $response);
    }

    public function testGetCustomerSubresource()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'customer']
        );

        $this->assertResponseContains('get_subresource_opportunity_customer.yml', $response);
    }

    public function testGetCustomerSubresourceWithIncludeFilter()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'customer'],
            [
                'fields[b2bcustomers]' => 'name,organization',
                'include'              => 'organization'
            ]
        );

        $this->assertResponseContains('get_subresource_opportunity_customer_include.yml', $response);
    }

    public function testGetCustomerSubresourceWithTitle()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'customer'],
            ['meta' => 'title']
        );

        $this->assertResponseContains('get_subresource_opportunity_customer_title.yml', $response);
    }

    public function testGetCustomerSubresourceWithIncludeFilterAndTitle()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'customer'],
            [
                'fields[b2bcustomers]' => 'name,organization',
                'include'              => 'organization',
                'meta'                 => 'title'
            ]
        );

        $this->assertResponseContains('get_subresource_opportunity_customer_include_title.yml', $response);
    }

    public function testUpdateLead()
    {
        $opportunityId = $this->getReference('opportunity1')->getId();
        $leadId = $this->getReference('lead2')->getId();
        $response = $this->patch(
            ['entity' => 'opportunities', 'id' => $opportunityId],
            [
                'data' => [
                    'type'          => 'opportunities',
                    'id'            => (string)$opportunityId,
                    'relationships' => [
                        'lead' => [
                            'data' => [
                                'type' => 'leads',
                                'id'   => (string)$leadId
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertResponseContains('update_opportunity_lead.yml', $response);

        // test that the lead was changed
        $opportunity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        $lead = $opportunity->getLead();
        self::assertInstanceOf(Lead::class, $lead);
        self::assertSame($leadId, $lead->getId());
    }

    public function testUpdateAccount()
    {
        $opportunityId = $this->getReference('opportunity1')->getId();
        $accountId = $this->getReference('account2')->getId();
        $response = $this->patch(
            ['entity' => 'opportunities', 'id' => $opportunityId],
            [
                'data' => [
                    'type'          => 'opportunities',
                    'id'            => (string)$opportunityId,
                    'relationships' => [
                        'account' => [
                            'data' => [
                                'type' => 'accounts',
                                'id'   => (string)$accountId
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertResponseContains('update_opportunity_account.yml', $response);

        // test that the account was changed
        $opportunity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        $account = $opportunity->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($accountId, $account->getId());
        self::assertNull($opportunity->getCustomerAssociation()->getCustomerTarget());
    }

    public function testUpdateCustomer()
    {
        $opportunityId = $this->getReference('opportunity1')->getId();
        $customerId = $this->getReference('b2b_customer2')->getId();
        $response = $this->patch(
            ['entity' => 'opportunities', 'id' => $opportunityId],
            [
                'data' => [
                    'type'          => 'opportunities',
                    'id'            => (string)$opportunityId,
                    'relationships' => [
                        'customer' => [
                            'data' => [
                                'type' => 'b2bcustomers',
                                'id'   => (string)$customerId
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertResponseContains('update_opportunity_customer.yml', $response);

        // test that the customer was changed
        $opportunity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        $customer = $opportunity->getCustomerAssociation()->getCustomerTarget();
        self::assertInstanceOf(B2bCustomer::class, $customer);
        self::assertSame($customerId, $customer->getId());
        $account = $opportunity->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($this->getReference('account2')->getId(), $account->getId());
    }

    public function testTryToUpdateWithInconsistentCustomer()
    {
        $response = $this->patch(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>'],
            $this->getRequestData('update_opportunity_inconsistent_customer.yml'),
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'form constraint',
                'detail' => 'The customer should be a part of the specified account.',
                'source' => ['pointer' => '/data/relationships/customer/data']
            ],
            $response
        );
    }

    public function testUpdateLeadAsRelationship()
    {
        $opportunityId = $this->getReference('opportunity1')->getId();
        $leadId = $this->getReference('lead2')->getId();
        $this->patchRelationship(
            ['entity' => 'opportunities', 'id' => $opportunityId, 'association' => 'lead'],
            [
                'data' => [
                    'type' => 'leads',
                    'id'   => (string)$leadId
                ]
            ]
        );

        // test that the lead was changed
        $opportunity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        $lead = $opportunity->getLead();
        self::assertInstanceOf(Lead::class, $lead);
        self::assertSame($leadId, $lead->getId());
    }

    public function testUpdateAccountAsRelationship()
    {
        $opportunityId = $this->getReference('opportunity1')->getId();
        $accountId = $this->getReference('account2')->getId();
        $this->patchRelationship(
            ['entity' => 'opportunities', 'id' => $opportunityId, 'association' => 'account'],
            [
                'data' => [
                    'type' => 'accounts',
                    'id'   => (string)$accountId
                ]
            ]
        );

        // test that the account was changed
        $opportunity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        $account = $opportunity->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($accountId, $account->getId());
        self::assertNull($opportunity->getCustomerAssociation()->getCustomerTarget());
    }

    public function testUpdateCustomerAsRelationship()
    {
        $opportunityId = $this->getReference('opportunity1')->getId();
        $customerId = $this->getReference('b2b_customer2')->getId();
        $this->patchRelationship(
            ['entity' => 'opportunities', 'id' => $opportunityId, 'association' => 'customer'],
            [
                'data' => [
                    'type' => 'b2bcustomers',
                    'id'   => (string)$customerId
                ]
            ]
        );

        // test that the customer was changed
        $opportunity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        $customer = $opportunity->getCustomerAssociation()->getCustomerTarget();
        self::assertInstanceOf(B2bCustomer::class, $customer);
        self::assertSame($customerId, $customer->getId());
        $account = $opportunity->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($this->getReference('account2')->getId(), $account->getId());
    }

    public function testTryToUpdateAccountAsRelationshipWithNullValue()
    {
        $response = $this->patchRelationship(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'account'],
            ['data' => null],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not null constraint',
                'detail' => 'This value should not be null.'
            ],
            $response
        );
    }

    public function testTryToUpdateCustomerAsRelationshipWithNullValue()
    {
        $response = $this->patchRelationship(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'customer'],
            ['data' => null],
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not null constraint',
                'detail' => 'This value should not be null.'
            ],
            $response
        );
    }
}
