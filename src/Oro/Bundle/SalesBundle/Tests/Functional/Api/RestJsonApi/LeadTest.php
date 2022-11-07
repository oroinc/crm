<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryEmailTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryPhoneTestTrait;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures\LoadLeadsData;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class LeadTest extends RestJsonApiTestCase
{
    use PrimaryEmailTestTrait;
    use PrimaryPhoneTestTrait;

    private const ENTITY_CLASS = Lead::class;
    private const ENTITY_TYPE = 'leads';
    private const CREATE_MIN_REQUEST_DATA = 'create_lead_min.yml';
    private const ENTITY_WITHOUT_EMAILS_REF = 'lead2';
    private const ENTITY_WITH_EMAILS_REF = 'lead1';
    private const PRIMARY_EMAIL = 'lead1_2@example.com';
    private const NOT_PRIMARY_EMAIL = 'lead1_1@example.com';
    private const ENTITY_WITHOUT_PHONES_REF = 'lead2';
    private const ENTITY_WITH_PHONES_REF = 'lead1';
    private const PRIMARY_PHONE = '5556661112';
    private const NOT_PRIMARY_PHONE = '5556661111';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadLeadsData::class]);
    }

    /**
     * @dataProvider cgetDataProvider
     */
    public function testGetList(array $parameters, string $expectedDataFileName)
    {
        $response = $this->cget(['entity' => 'leads'], $parameters);

        $this->assertResponseContains($expectedDataFileName, $response);
    }

    public function cgetDataProvider(): array
    {
        return [
            'without parameters'                                                        => [
                'parameters'      => [],
                'expectedContent' => 'cget_lead.yml'
            ],
            'filter by status'                                                          => [
                'parameters'      => [
                    'filter' => ['status' => 'new']
                ],
                'expectedContent' => 'cget_lead_filter_by_status.yml'
            ],
            'fields and include filters for customer association'                       => [
                'parameters'      => [
                    'fields[leads]'    => 'name,account,customer',
                    'fields[accounts]' => 'name,organization',
                    'include'          => 'account,customer'
                ],
                'expectedContent' => 'cget_lead_customer_association.yml'
            ],
            'fields and include filters for nested association of customer association' => [
                'parameters'      => [
                    'fields[leads]'         => 'account',
                    'fields[accounts]'      => 'name,organization',
                    'fields[organizations]' => 'name',
                    'include'               => 'account,account.organization'
                ],
                'expectedContent' => 'cget_lead_customer_association_nested.yml'
            ],
            'title for customer association'                                            => [
                'parameters'      => [
                    'meta'             => 'title',
                    'fields[leads]'    => 'name,account,customer',
                    'fields[accounts]' => 'name,organization',
                    'include'          => 'account,customer'
                ],
                'expectedContent' => 'cget_lead_customer_association_title.yml'
            ]
        ];
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'leads', 'id' => $this->getReference('lead1')->getId()]
        );

        $this->assertResponseContains('get_lead.yml', $response);
    }

    public function testCreate()
    {
        $response = $this->post(
            ['entity' => 'leads'],
            'create_lead.yml'
        );

        $this->assertResponseContains('create_lead.yml', $response);

        // test that the entity was created
        $entity = $this->getEntityManager()->find(Lead::class, $this->getResourceId($response));
        self::assertNotNull($entity);
    }

    public function testCreateWithNullCreatedAtAndUpdatedAt()
    {
        $data = $this->getRequestData('create_lead_min.yml');
        $data['data']['attributes']['createdAt'] = null;
        $data['data']['attributes']['updatedAt'] = null;
        $response = $this->post(
            ['entity' => 'leads'],
            $data
        );

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity = $this->getEntityManager()->find(Lead::class, $this->getResourceId($response));
        self::assertTrue($entity->getCreatedAt() !== null && $entity->getCreatedAt() <= $now);
        self::assertTrue($entity->getUpdatedAt() !== null && $entity->getUpdatedAt() <= $now);
    }

    public function testCreateWithoutAccountAndCustomer()
    {
        $response = $this->post(
            ['entity' => 'leads'],
            'create_lead_no_account_and_customer.yml'
        );

        $this->assertResponseContains('create_lead_no_account_and_customer.yml', $response);

        // test that the entity was created
        $entity = $this->getEntityManager()->find(Lead::class, $this->getResourceId($response));
        self::assertNotNull($entity);
    }

    public function testCreateWithoutStatus()
    {
        $response = $this->post(
            ['entity' => 'leads'],
            'create_lead_no_status.yml'
        );

        $this->assertResponseContains('create_lead_no_status.yml', $response);

        // test that the entity was created
        $entity = $this->getEntityManager()->find(Lead::class, $this->getResourceId($response));
        self::assertNotNull($entity);
    }

    public function testTryToCreateWithNullStatus()
    {
        $response = $this->post(
            ['entity' => 'leads'],
            'create_lead_null_status.yml',
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/relationships/status/data']
            ],
            $response
        );
    }

    public function testTryToCreateWithInconsistentCustomer()
    {
        $response = $this->post(
            ['entity' => 'leads'],
            $this->getRequestData('create_lead_inconsistent_customer.yml'),
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
            ['entity' => 'leads'],
            'create_lead_consistent_customer.yml'
        );

        $this->assertResponseContains('create_lead_consistent_customer.yml', $response);
    }

    public function testUpdateWithNullCreatedAtAndUpdatedAt()
    {
        /** @var Lead $lead */
        $lead = $this->getReference('lead1');
        $leadId = $lead->getId();
        $createdAt = $lead->getCreatedAt();
        $updatedAt = $lead->getUpdatedAt();
        $data = [
            'data' => [
                'type'       => 'leads',
                'id'         => (string)$leadId,
                'attributes' => [
                    'createdAt' => null,
                    'updatedAt' => null
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'leads', 'id' => (string)$leadId],
            $data
        );

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity = $this->getEntityManager()->find(Lead::class, $leadId);
        self::assertEquals($createdAt, $entity->getCreatedAt());
        self::assertTrue($entity->getUpdatedAt() >= $updatedAt && $entity->getUpdatedAt() <= $now);
    }

    public function testUpdateShouldIgnoreCreatedAtAndUpdatedAt()
    {
        /** @var Lead $lead */
        $lead = $this->getReference('lead1');
        $leadId = $lead->getId();
        $createdAt = $lead->getCreatedAt();
        $updatedAt = $lead->getUpdatedAt();
        $data = [
            'data' => [
                'type'       => 'leads',
                'id'         => (string)$leadId,
                'attributes' => [
                    'createdAt' => '2019-01-01T01:01:01Z',
                    'updatedAt' => '2019-01-01T01:01:01Z'
                ]
            ]
        ];
        $this->patch(
            ['entity' => 'leads', 'id' => (string)$leadId],
            $data
        );

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $entity = $this->getEntityManager()->find(Lead::class, $leadId);
        self::assertEquals($createdAt, $entity->getCreatedAt());
        self::assertTrue($entity->getUpdatedAt() >= $updatedAt && $entity->getUpdatedAt() <= $now);
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

        $this->assertResponseContains('get_relationship_lead_account.yml', $response);
    }

    /**
     * @dataProvider getAccountSubresourceDataProvider
     */
    public function testGetAccountSubresource(array $parameters, string $expectedDataFileName)
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

    public function getAccountSubresourceDataProvider(): array
    {
        return [
            'without parameters'         => [
                'parameters'      => [],
                'expectedContent' => 'get_subresource_lead_account.yml'
            ],
            'fields and include filters' => [
                'parameters'      => [
                    'fields[accounts]' => 'organization',
                    'include'          => 'organization'
                ],
                'expectedContent' => 'get_subresource_lead_account_include.yml'
            ],
            'title meta'                 => [
                'parameters'      => [
                    'meta' => 'title'
                ],
                'expectedContent' => 'get_subresource_lead_account_title.yml'
            ]
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

        $this->assertResponseContains('get_relationship_lead_customer.yml', $response);
    }

    /**
     * @dataProvider getCustomerSubresourceDataProvider
     */
    public function testGetCustomerSubresource(array $parameters, string $expectedDataFileName)
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

    public function getCustomerSubresourceDataProvider(): array
    {
        return [
            'without parameters'         => [
                'parameters'      => [],
                'expectedContent' => 'get_subresource_lead_customer.yml'
            ],
            'fields and include filters' => [
                'parameters'      => [
                    'fields[b2bcustomers]' => 'organization',
                    'include'              => 'organization'
                ],
                'expectedContent' => 'get_subresource_lead_customer_include.yml'
            ],
            'title meta'                 => [
                'parameters'      => [
                    'meta' => 'title'
                ],
                'expectedContent' => 'get_subresource_lead_customer_title.yml'
            ]
        ];
    }

    public function testUpdateAccount()
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
                                'id'   => (string)$accountId
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertResponseContains('update_lead_account.yml', $response);

        // test that the account was changed
        $lead = $this->getEntityManager()->find(Lead::class, $leadId);
        $account = $lead->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($accountId, $account->getId());
        self::assertNull($lead->getCustomerAssociation()->getCustomerTarget());
    }

    public function testUpdateCustomer()
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
                                'id'   => (string)$customerId
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertResponseContains('update_lead_customer.yml', $response);

        // test that the customer was changed
        $lead = $this->getEntityManager()->find(Lead::class, $leadId);
        $customer = $lead->getCustomerAssociation()->getCustomerTarget();
        self::assertInstanceOf(B2bCustomer::class, $customer);
        self::assertSame($customerId, $customer->getId());
        $account = $lead->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($this->getReference('account2')->getId(), $account->getId());
    }

    public function testTryToUpdateWithInconsistentCustomer()
    {
        $leadId = $this->getReference('lead1')->getId();
        $response = $this->patch(
            ['entity' => 'leads', 'id' => $leadId],
            $this->getRequestData('update_lead_inconsistent_customer.yml'),
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

    public function testTryToUpdateWithNullStatus()
    {
        $leadId = $this->getReference('lead1')->getId();
        $response = $this->patch(
            ['entity' => 'leads', 'id' => $leadId],
            $this->getRequestData('update_lead_null_status.yml'),
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/relationships/status/data']
            ],
            $response
        );
    }

    public function testUpdateAccountAsRelationship()
    {
        $leadId = $this->getReference('lead1')->getId();
        $accountId = $this->getReference('account2')->getId();
        $this->patchRelationship(
            ['entity' => 'leads', 'id' => $leadId, 'association' => 'account'],
            [
                'data' => [
                    'type' => 'accounts',
                    'id'   => (string)$accountId
                ]
            ]
        );

        // test that the account was changed
        $lead = $this->getEntityManager()->find(Lead::class, $leadId);
        $account = $lead->getCustomerAssociation()->getAccount();
        self::assertInstanceOf(Account::class, $account);
        self::assertSame($accountId, $account->getId());
        self::assertNull($lead->getCustomerAssociation()->getCustomerTarget());
    }

    public function testUpdateCustomerAsRelationship()
    {
        $leadId = $this->getReference('lead1')->getId();
        $customerId = $this->getReference('b2b_customer2')->getId();
        $this->patchRelationship(
            ['entity' => 'leads', 'id' => $leadId, 'association' => 'customer'],
            [
                'data' => [
                    'type' => 'b2bcustomers',
                    'id'   => (string)$customerId
                ]
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

    public function testTryToUpdateAccountAsRelationshipWithNullValue()
    {
        $leadId = $this->getReference('lead1')->getId();
        $response = $this->patchRelationship(
            ['entity' => 'leads', 'id' => $leadId, 'association' => 'account'],
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
        $leadId = $this->getReference('lead1')->getId();
        $response = $this->patchRelationship(
            ['entity' => 'leads', 'id' => $leadId, 'association' => 'customer'],
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
