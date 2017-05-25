<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures\LoadLeadsData;

class LeadApiTest extends RestJsonApiTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadFixtures([LoadLeadsData::class]);
    }

    /**
     * @param array  $parameters
     * @param string $expectedDataFileName
     *
     * @dataProvider cgetDataProvider
     */
    public function testCget(array $parameters, $expectedDataFileName)
    {
        $response = $this->cget(['entity' => 'leads'], $parameters);

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
                'expectedContent' => 'lead_cget.yml',
            ],
            'filter by status'                                    => [
                'parameters'      => [
                    'filter' => [
                        'status' => 'new',
                    ],
                ],
                'expectedContent' => 'lead_cget_filter_by_status.yml',
            ],
            'fields and include filters for customer association' => [
                'parameters'      => [
                    'fields[opportunities]' => 'account,customer',
                    'include'               => 'account,customer',
                ],
                'expectedContent' => 'lead_cget_customer_association.yml',
            ],
            'title for customer association'                      => [
                'parameters'      => [
                    'meta'                  => 'title',
                    'fields[opportunities]' => 'account,customer',
                    'include'               => 'account,customer',
                ],
                'expectedContent' => 'lead_cget_customer_association_title.yml',
            ],
        ];
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'leads', 'id' => $this->getReference('lead1')->getId()]
        );

        $this->assertResponseContains('lead_get.yml', $response);
    }

    public function testPost()
    {
        $response = $this->post(
            ['entity' => 'leads'],
            'lead_post.yml'
        );

        $this->assertResponseContains('lead_post.yml', $response);

        // test that the entity was created
        $entity = $this->getEntityManager()->find(Lead::class, $this->getResourceId($response));
        self::assertNotNull($entity);
    }

    public function testPostWithoutAccountAndCustomer()
    {
        $response = $this->request(
            'POST',
            $this->getUrl('oro_rest_api_post', ['entity' => 'leads']),
            $this->getRequestData('lead_post_no_account_and_customer.yml')
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
            $this->getUrl('oro_rest_api_post', ['entity' => 'leads']),
            $this->getRequestData('lead_post_inconsistent_customer.yml')
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
            ['entity' => 'leads'],
            'lead_post_consistent_customer.yml'
        );

        $this->assertResponseContains('lead_post_consistent_customer.yml', $response);
    }

    public function testGetAccountRelationship()
    {
        $response = $this->getRelationship(
            [
                'entity'      => 'leads',
                'id'          => $this->getReference('lead1')->getId(),
                'association' => 'account'
            ]
        );

        $this->assertResponseContains('lead_get_relationship_account.yml', $response);
    }

    /**
     * @param array  $parameters
     * @param string $expectedDataFileName
     *
     * @dataProvider getAccountSubresourceDataProvider
     */
    public function testGetAccountSubresource(array $parameters, $expectedDataFileName)
    {
        $this->assertNotEmpty($this->getReference('organization')->getName());

        $response = $this->getSubresource(
            [
                'entity'      => 'leads',
                'id'          => $this->getReference('lead1')->getId(),
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
                'expectedContent' => 'lead_get_subresource_account.yml',
            ],
            'fields and include filters' => [
                'parameters'      => [
                    'fields[accounts]' => 'organization',
                    'include'          => 'organization',
                ],
                'expectedContent' => 'lead_get_subresource_account_include.yml',
            ],
            'title meta'                 => [
                'parameters'      => [
                    'meta' => 'title',
                ],
                'expectedContent' => 'lead_get_subresource_account_title.yml',
            ],
        ];
    }

    public function testGetCustomerRelationship()
    {
        $response = $this->getRelationship(
            [
                'entity'      => 'leads',
                'id'          => $this->getReference('lead1')->getId(),
                'association' => 'customer'
            ]
        );

        $this->assertResponseContains('lead_get_relationship_customer.yml', $response);
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
                'entity'      => 'leads',
                'id'          => $this->getReference('lead1')->getId(),
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
                'expectedContent' => 'lead_get_subresource_customer.yml',
            ],
            'fields and include filters' => [
                'parameters'      => [
                    'fields[b2bcustomers]' => 'organization',
                    'include'              => 'organization',
                ],
                'expectedContent' => 'lead_get_subresource_customer_include.yml',
            ],
            'title meta'                 => [
                'parameters'      => [
                    'meta' => 'title',
                ],
                'expectedContent' => 'lead_get_subresource_customer_title.yml',
            ],
        ];
    }

    public function testPatchAccount()
    {
        $leadId = $this->getReference('lead1')->getId();
        $accountId = $this->getReference('account2')->getId();
        $response = $this->patch(
            ['entity' => 'leads', 'id' => $leadId],
            [
                'data' => [
                    'type'          => 'leads',
                    'id'            => (string)$leadId,
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

        $this->assertResponseContains('lead_patch_account.yml', $response);

        // test that the account was changed
        $lead = $this->getEntityManager()->find(Lead::class, $leadId);
        $account = $lead->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($accountId, $account->getId());
        self::assertNull($lead->getCustomerAssociation()->getCustomerTarget());
    }

    public function testPatchCustomer()
    {
        $leadId = $this->getReference('lead1')->getId();
        $customerId = $this->getReference('b2b_customer2')->getId();
        $response = $this->patch(
            ['entity' => 'leads', 'id' => $leadId],
            [
                'data' => [
                    'type'          => 'leads',
                    'id'            => (string)$leadId,
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

        $this->assertResponseContains('lead_patch_customer.yml', $response);

        // test that the customer was changed
        $lead = $this->getEntityManager()->find(Lead::class, $leadId);
        $customer = $lead->getCustomerAssociation()->getCustomerTarget();
        self::assertInstanceOf(B2bCustomer::class, $customer);
        self::assertSame($customerId, $customer->getId());
        $account = $lead->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($this->getReference('account2')->getId(), $account->getId());
    }

    public function testPatchWithInconsistentCustomer()
    {
        $leadId = $this->getReference('lead1')->getId();
        $response = $this->request(
            'PATCH',
            $this->getUrl('oro_rest_api_patch', ['entity' => 'leads', 'id' => $leadId]),
            $this->getRequestData('lead_patch_inconsistent_customer.yml')
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

    public function testPatchAccountAsRelationship()
    {
        $leadId = $this->getReference('lead1')->getId();
        $accountId = $this->getReference('account2')->getId();
        $this->patchRelationship(
            ['entity' => 'leads', 'id' => $leadId, 'association' => 'account'],
            [
                'data' => [
                    'type' => 'accounts',
                    'id'   => (string)$accountId,
                ],
            ]
        );

        // test that the account was changed
        $lead = $this->getEntityManager()->find(Lead::class, $leadId);
        $account = $lead->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($accountId, $account->getId());
        self::assertNull($lead->getCustomerAssociation()->getCustomerTarget());
    }

    public function testPatchCustomerAsRelationship()
    {
        $leadId = $this->getReference('lead1')->getId();
        $customerId = $this->getReference('b2b_customer2')->getId();
        $this->patchRelationship(
            ['entity' => 'leads', 'id' => $leadId, 'association' => 'customer'],
            [
                'data' => [
                    'type' => 'b2bcustomers',
                    'id'   => (string)$customerId,
                ],
            ]
        );

        // test that the customer was changed
        $lead = $this->getEntityManager()->find(Lead::class, $leadId);
        $customer = $lead->getCustomerAssociation()->getCustomerTarget();
        self::assertInstanceOf(B2bCustomer::class, $customer);
        self::assertSame($customerId, $customer->getId());
        $account = $lead->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($this->getReference('account2')->getId(), $account->getId());
    }

    public function testPatchAccountAsRelationshipWithNullValue()
    {
        $leadId = $this->getReference('lead1')->getId();
        $response = $this->request(
            'PATCH',
            $this->getUrl(
                'oro_rest_api_patch_relationship',
                ['entity' => 'leads', 'id' => $leadId, 'association' => 'account']
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
        $leadId = $this->getReference('lead1')->getId();
        $response = $this->request(
            'PATCH',
            $this->getUrl(
                'oro_rest_api_patch_relationship',
                ['entity' => 'leads', 'id' => $leadId, 'association' => 'customer']
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
