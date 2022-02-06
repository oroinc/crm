<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryEmailTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryPhoneTestTrait;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

/**
 * @dbIsolationPerTest
 */
class B2bCustomerTest extends RestJsonApiTestCase
{
    use PrimaryEmailTestTrait;
    use PrimaryPhoneTestTrait;

    private const ENTITY_CLASS              = B2bCustomer::class;
    private const ENTITY_TYPE               = 'b2bcustomers';
    private const CREATE_MIN_REQUEST_DATA   = 'create_b2b_customer_min.yml';
    private const ENTITY_WITHOUT_EMAILS_REF = 'customer2';
    private const ENTITY_WITH_EMAILS_REF    = 'customer1';
    private const PRIMARY_EMAIL             = 'customer1_2@example.com';
    private const NOT_PRIMARY_EMAIL         = 'customer1_1@example.com';
    private const ENTITY_WITHOUT_PHONES_REF = 'customer2';
    private const ENTITY_WITH_PHONES_REF    = 'customer1';
    private const PRIMARY_PHONE             = '5556661112';
    private const NOT_PRIMARY_PHONE         = '5556661111';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(['@OroSalesBundle/Tests/Functional/Api/DataFixtures/b2b_customers.yml']);
    }
}
