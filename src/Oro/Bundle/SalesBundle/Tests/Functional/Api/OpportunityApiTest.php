<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures\LoadOpportunitiesData;

class OpportunityApiTest extends RestJsonApiTestCase
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
     */
    public function cgetDataProvider()
    {
        return [
            'without parameters'                                  => [
                'parameters'      => [],
                'expectedContent' => 'opportunity_cget.yml',
            ],
            'filter by status'                                    => [
                'parameters'      => [
                    'filter' => [
                        'status' => 'won',
                    ],
                ],
                'expectedContent' => 'opportunity_cget_filter_by_status.yml',
            ],
            'fields and include filters for customer association' => [
                'parameters'      => [
                    'fields[opportunities]' => 'account,customer',
                    'include'               => 'account,customer',
                ],
                'expectedContent' => 'opportunity_cget_customer_association.yml',
            ],
            'title for customer association'                      => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'account,customer',
                    'include'               => 'account,customer',
                ],
                'expectedContent' => 'opportunity_cget_customer_association_title.yml',
            ],
            'fields and include filters for nested customer association' => [
                'parameters'      => [
                    'fields[opportunities]' => 'lead',
                    'fields[leads]'         => 'account,customer',
                    'include'               => 'lead.account,lead.customer',
                ],
                'expectedContent' => 'opportunity_cget_customer_association_nested.yml',
            ],
            'title for nested customer association'                      => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'lead',
                    'fields[leads]'         => 'account,customer',
                    'include'               => 'lead.account,lead.customer',
                ],
                'expectedContent' => 'opportunity_cget_customer_association_nested_title.yml',
            ],
        ];
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'opportunities', 'id' => $this->getReference('opportunity1')->getId()]
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
        $response = $this->request(
            'POST',
            $this->getUrl('oro_rest_api_post', ['entity' => 'opportunities']),
            $this->getRequestData('opportunity_post_no_account_and_customer.yml')
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        self::assertEquals(
            [
                [
                    'status' => '400',
                    'title'  => 'form constraint',
                    'detail' => 'Either an account or a customer should be set.'
                ]
            ],
            $this->getResponseErrors($response)
        );
    }

    public function testPostWithInconsistentCustomer()
    {
        $response = $this->request(
            'POST',
            $this->getUrl('oro_rest_api_post', ['entity' => 'opportunities']),
            $this->getRequestData('opportunity_post_inconsistent_customer.yml')
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        self::assertEquals(
            [
                [
                    'status' => '400',
                    'title'  => 'form constraint',
                    'detail' => 'The customer should be a part of the specified account.',
                    'source' => [
                        'pointer' => '/data/relationships/customer/data'
                    ]
                ]
            ],
            $this->getResponseErrors($response)
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
            [
                'entity'      => 'opportunities',
                'id'          => $this->getReference('opportunity1')->getId(),
                'association' => 'lead'
            ]
        );

        $this->assertResponseContains('opportunity_get_relationship_lead', $response);
    }

    public function testGetLeadSubresource()
    {
        $response = $this->getSubresource(
            [
                'entity'      => 'opportunities',
                'id'          => $this->getReference('opportunity1')->getId(),
                'association' => 'lead'
            ]
        );

        $this->assertResponseContains('opportunity_get_subresource_lead', $response);
    }

    public function testGetAccountRelationship()
    {
        $response = $this->getRelationship(
            [
                'entity'      => 'opportunities',
                'id'          => $this->getReference('opportunity1')->getId(),
                'association' => 'account'
            ]
        );

        $this->assertResponseContains('opportunity_get_relationship_account.yml', $response);
    }

    /**
     * @param array  $parameters
     * @param string $expectedDataFileName
     *
     * @dataProvider getAccountSubresourceDataProvider
     */
    public function testGetAccountSubresource(array $parameters, $expectedDataFileName)
    {
        $response = $this->getSubresource(
            [
                'entity'      => 'opportunities',
                'id'          => $this->getReference('opportunity1')->getId(),
                'association' => 'account'
            ],
            $parameters
        );

        $this->assertResponseContains($expectedDataFileName, $response);
    }

    /**
     * @return array
     */
    public function getAccountSubresourceDataProvider()
    {
        return [
            'without parameters'         => [
                'parameters'      => [],
                'expectedContent' => 'opportunity_get_subresource_account.yml',
            ],
            'fields and include filters' => [
                'parameters'      => [
                    'fields[accounts]' => 'organization',
                    'include'          => 'organization',
                ],
                'expectedContent' => 'opportunity_get_subresource_account_include.yml',
            ],
            'title meta'                 => [
                'parameters'      => [
                    'meta' => 'title',
                ],
                'expectedContent' => 'opportunity_get_subresource_account_title.yml',
            ],
        ];
    }

    public function testGetCustomerRelationship()
    {
        $response = $this->getRelationship(
            [
                'entity'      => 'opportunities',
                'id'          => $this->getReference('opportunity1')->getId(),
                'association' => 'customer'
            ]
        );

        $this->assertResponseContains('opportunity_get_relationship_customer.yml', $response);
    }

    /**
     * @param array  $parameters
     * @param string $expectedDataFileName
     *
     * @dataProvider getCustomerSubresourceDataProvider
     */
    public function testGetCustomerSubresource(array $parameters, $expectedDataFileName)
    {
        $response = $this->getSubresource(
            [
                'entity'      => 'opportunities',
                'id'          => $this->getReference('opportunity1')->getId(),
                'association' => 'customer'
            ],
            $parameters
        );

        $this->assertResponseContains($expectedDataFileName, $response);
    }

    /**
     * @return array
     */
    public function getCustomerSubresourceDataProvider()
    {
        return [
            'without parameters'         => [
                'parameters'      => [],
                'expectedContent' => 'opportunity_get_subresource_customer.yml',
            ],
            'fields and include filters' => [
                'parameters'      => [
                    'fields[b2bcustomers]' => 'organization',
                    'include'              => 'organization',
                ],
                'expectedContent' => 'opportunity_get_subresource_customer_include.yml',
            ],
            'title meta'                 => [
                'parameters'      => [
                    'meta' => 'title',
                ],
                'expectedContent' => 'opportunity_get_subresource_customer_title.yml',
            ],
        ];
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
                                'id'   => (string)$leadId,
                            ],
                        ],
                    ],
                ],
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
                                'id'   => (string)$accountId,
                            ],
                        ],
                    ],
                ],
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
                                'id'   => (string)$customerId,
                            ],
                        ],
                    ],
                ],
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
        $opportunityId = $this->getReference('opportunity1')->getId();
        $response = $this->request(
            'PATCH',
            $this->getUrl('oro_rest_api_patch', ['entity' => 'opportunities', 'id' => $opportunityId]),
            $this->getRequestData('opportunity_patch_inconsistent_customer.yml')
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        self::assertEquals(
            [
                [
                    'status' => '400',
                    'title'  => 'form constraint',
                    'detail' => 'The customer should be a part of the specified account.',
                    'source' => [
                        'pointer' => '/data/relationships/customer/data'
                    ]
                ]
            ],
            $this->getResponseErrors($response)
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
                    'id'   => (string)$leadId,
                ],
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
                    'id'   => (string)$accountId,
                ],
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
                    'id'   => (string)$customerId,
                ],
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
        $opportunityId = $this->getReference('opportunity1')->getId();
        $response = $this->request(
            'PATCH',
            $this->getUrl(
                'oro_rest_api_patch_relationship',
                ['entity' => 'opportunities', 'id' => $opportunityId, 'association' => 'account']
            ),
            [
                'data' => null
            ]
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        self::assertEquals(
            [
                [
                    'status' => '400',
                    'title'  => 'not null constraint',
                    'detail' => 'This value should not be null.'
                ]
            ],
            $this->getResponseErrors($response)
        );
    }

    public function testPatchCustomerAsRelationshipWithNullValue()
    {
        $opportunityId = $this->getReference('opportunity1')->getId();
        $response = $this->request(
            'PATCH',
            $this->getUrl(
                'oro_rest_api_patch_relationship',
                ['entity' => 'opportunities', 'id' => $opportunityId, 'association' => 'customer']
            ),
            [
                'data' => null
            ]
        );

        self::assertResponseStatusCodeEquals($response, Response::HTTP_BAD_REQUEST);
        self::assertEquals(
            [
                [
                    'status' => '400',
                    'title'  => 'not null constraint',
                    'detail' => 'This value should not be null.'
                ]
            ],
            $this->getResponseErrors($response)
        );
    }
}
