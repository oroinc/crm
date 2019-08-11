<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures\LoadOpportunitiesData;

class OpportunityTest extends RestJsonApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadOpportunitiesData::class]);
    }

    /**
     * @param array  $parameters
     * @param string $expectedDataFileName
     *
     * @dataProvider cgetDataProvider
     */
    public function testCget(array $parameters, $expectedDataFileName)
    {
        $response = $this->cget(['entity' => 'opportunities'], $parameters);

        $this->assertResponseContains($expectedDataFileName, $response);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function cgetDataProvider()
    {
        return [
            'without parameters'                                                 => [
                'parameters'      => [],
                'expectedContent' => 'opportunity_cget.yml'
            ],
            'filter by status'                                                   => [
                'parameters'      => [
                    'filter' => ['status' => 'won']
                ],
                'expectedContent' => 'opportunity_cget_filter_by_status.yml'
            ],
            'title without fields and include filters'                           => [
                'parameters'      => [
                    'meta' => 'title'
                ],
                'expectedContent' => 'opportunity_cget_title.yml'
            ],
            'fields and include filters for customer association'                => [
                'parameters'      => [
                    'fields[opportunities]' => 'account,customer',
                    'include'               => 'account,customer'
                ],
                'expectedContent' => 'opportunity_cget_customer_association.yml'
            ],
            'title for customer association'                                     => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'account,customer',
                    'include'               => 'account,customer'
                ],
                'expectedContent' => 'opportunity_cget_customer_association_title.yml'
            ],
            'fields and include filters for nested customer association'         => [
                'parameters'      => [
                    'fields[opportunities]' => 'lead,account,customer',
                    'fields[leads]'         => 'account,customer',
                    'include'               => 'lead.account,lead.customer'
                ],
                'expectedContent' => 'opportunity_cget_customer_association_nested.yml'
            ],
            'title for nested customer association'                              => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'lead',
                    'fields[leads]'         => 'account,customer',
                    'include'               => 'lead.account,lead.customer'
                ],
                'expectedContent' => 'opportunity_cget_customer_association_nested_title.yml'
            ],
            'fields and include filters for nested association of lead.account'  => [
                'parameters'      => [
                    'fields[opportunities]' => 'lead,account,customer',
                    'fields[leads]'         => 'account',
                    'fields[accounts]'      => 'name,organization',
                    'fields[organizations]' => 'name,users',
                    'include'               => 'lead.account.organization'
                ],
                'expectedContent' => 'opportunity_cget_customer_association_nested1.yml'
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
                'expectedContent' => 'opportunity_cget_customer_association_nested1_title.yml'
            ],
            'fields and include filters for nested association of lead.customer' => [
                'parameters'      => [
                    'fields[opportunities]' => 'lead,account,customer',
                    'fields[leads]'         => 'customer',
                    'fields[b2bcustomers]'  => 'name,organization',
                    'fields[organizations]' => 'name,users',
                    'include'               => 'lead.customer.organization'
                ],
                'expectedContent' => 'opportunity_cget_customer_association_nested2.yml'
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
                'expectedContent' => 'opportunity_cget_customer_association_nested2_title.yml'
            ],
            'title for close reason and status'                                  => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'closeReason,status',
                    'include'               => 'closeReason,status'
                ],
                'expectedContent' => 'opportunity_cget_dictionary_title.yml'
            ]
        ];
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>']
        );

        $this->assertResponseContains('opportunity_get.yml', $response);
    }

    public function testPost()
    {
        $response = $this->post(
            ['entity' => 'opportunities'],
            'opportunity_post.yml'
        );

        $this->assertResponseContains('opportunity_post.yml', $response);

        // test that the entity was created
        $entity = $this->getEntityManager()->find(Opportunity::class, $this->getResourceId($response));
        self::assertNotNull($entity);
    }

    public function testPostWithoutAccountAndCustomer()
    {
        $response = $this->post(
            ['entity' => 'opportunities'],
            $this->getRequestData('opportunity_post_no_account_and_customer.yml'),
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

    public function testPostWithInconsistentCustomer()
    {
        $response = $this->post(
            ['entity' => 'opportunities'],
            $this->getRequestData('opportunity_post_inconsistent_customer.yml'),
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

    public function testPostWithConsistentCustomer()
    {
        $response = $this->post(
            ['entity' => 'opportunities'],
            'opportunity_post_consistent_customer.yml'
        );

        $this->assertResponseContains('opportunity_post_consistent_customer.yml', $response);
    }

    public function testGetLeadRelationship()
    {
        $response = $this->getRelationship(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'lead']
        );

        $this->assertResponseContains('opportunity_get_relationship_lead.yml', $response);
    }

    public function testGetLeadSubresource()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'lead']
        );

        $this->assertResponseContains('opportunity_get_subresource_lead.yml', $response);
    }

    public function testGetAccountRelationship()
    {
        $response = $this->getRelationship(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'account']
        );

        $this->assertResponseContains('opportunity_get_relationship_account.yml', $response);
    }

    public function testGetAccountSubresource()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'account']
        );
        $this->assertResponseContains('opportunity_get_subresource_account.yml', $response);
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
        $this->assertResponseContains('opportunity_get_subresource_account_include.yml', $response);
    }

    public function testGetAccountSubresourceWithTitle()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'account'],
            ['meta' => 'title']
        );
        $this->assertResponseContains('opportunity_get_subresource_account_title.yml', $response);
    }

    public function testGetCustomerRelationship()
    {
        $response = $this->getRelationship(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'customer']
        );

        $this->assertResponseContains('opportunity_get_relationship_customer.yml', $response);
    }

    public function testGetCustomerSubresource()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'customer']
        );
        $this->assertResponseContains('opportunity_get_subresource_customer.yml', $response);
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
        $this->assertResponseContains('opportunity_get_subresource_customer_include.yml', $response);
    }

    public function testGetCustomerSubresourceWithTitle()
    {
        $response = $this->getSubresource(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>', 'association' => 'customer'],
            ['meta' => 'title']
        );
        $this->assertResponseContains('opportunity_get_subresource_customer_title.yml', $response);
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
        $this->assertResponseContains('opportunity_get_subresource_customer_include_title.yml', $response);
    }

    public function testPatchLead()
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

        $this->assertResponseContains('opportunity_patch_lead.yml', $response);

        // test that the lead was changed
        $opportunity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        $lead = $opportunity->getLead();
        self::assertInstanceOf(Lead::class, $lead);
        self::assertSame($leadId, $lead->getId());
    }

    public function testPatchAccount()
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

        $this->assertResponseContains('opportunity_patch_account.yml', $response);

        // test that the account was changed
        $opportunity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        $account = $opportunity->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($accountId, $account->getId());
        self::assertNull($opportunity->getCustomerAssociation()->getCustomerTarget());
    }

    public function testPatchCustomer()
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

        $this->assertResponseContains('opportunity_patch_customer.yml', $response);

        // test that the customer was changed
        $opportunity = $this->getEntityManager()->find(Opportunity::class, $opportunityId);
        $customer = $opportunity->getCustomerAssociation()->getCustomerTarget();
        self::assertInstanceOf(B2bCustomer::class, $customer);
        self::assertSame($customerId, $customer->getId());
        $account = $opportunity->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($this->getReference('account2')->getId(), $account->getId());
    }

    public function testPatchWithInconsistentCustomer()
    {
        $response = $this->patch(
            ['entity' => 'opportunities', 'id' => '<toString(@opportunity1->id)>'],
            $this->getRequestData('opportunity_patch_inconsistent_customer.yml'),
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

    public function testPatchLeadAsRelationship()
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

    public function testPatchAccountAsRelationship()
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

    public function testPatchCustomerAsRelationship()
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

    public function testPatchAccountAsRelationshipWithNullValue()
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

    public function testPatchCustomerAsRelationshipWithNullValue()
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
