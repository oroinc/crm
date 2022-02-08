<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Tests\Functional\DocumentationTestTrait;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;

/**
 * @group regression
 */
class B2bCustomerAccountDocumentationTest extends RestJsonApiTestCase
{
    use DocumentationTestTrait;

    /** @var string used in DocumentationTestTrait */
    private const VIEW = 'rest_json_api';

    private static bool $isDocumentationCacheWarmedUp = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (!self::$isDocumentationCacheWarmedUp) {
            $this->warmUpDocumentationCache();
            self::$isDocumentationCacheWarmedUp = true;
        }
    }

    public function testB2bCustomerAccount(): void
    {
        $docs = $this->getEntityDocsForAction('b2bcustomers', ApiAction::GET);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals(
            '<p>The account associated with the business customer record.</p>',
            $resourceData['response']['account']['description']
        );
    }

    public function testB2bCustomerAccountForCreate(): void
    {
        $docs = $this->getEntityDocsForAction('b2bcustomers', ApiAction::CREATE);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals(
            '<p>The account associated with the business customer record.</p>'
            . '<p><strong>If not specified, a new account will be created.</strong></p>',
            $resourceData['parameters']['account']['description']
        );
    }

    public function testB2bCustomerAccountForUpdate(): void
    {
        $docs = $this->getEntityDocsForAction('b2bcustomers', ApiAction::UPDATE);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals(
            '<p>The account associated with the business customer record.</p>'
            . '<p><strong>The required field.</strong></p>',
            $resourceData['parameters']['account']['description']
        );
    }

    public function testAccountB2bCustomers(): void
    {
        $docs = $this->getEntityDocsForAction('accounts', ApiAction::GET);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals(
            '<p>The business customers associated with the account record.</p>',
            $resourceData['response']['b2bCustomers']['description']
        );
    }

    public function testAccountB2bCustomersForCreate(): void
    {
        $docs = $this->getEntityDocsForAction('accounts', ApiAction::CREATE);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals(
            '<p>The business customers associated with the account record.</p>'
            . '<p><strong>The read-only field. A passed value will be ignored.</strong></p>',
            $resourceData['parameters']['b2bCustomers']['description']
        );
    }

    public function testAccountB2bCustomersForUpdate(): void
    {
        $docs = $this->getEntityDocsForAction('accounts', ApiAction::UPDATE);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals(
            '<p>The business customers associated with the account record.</p>'
            . '<p><strong>The read-only field. A passed value will be ignored.</strong></p>',
            $resourceData['parameters']['b2bCustomers']['description']
        );
    }

    public function testB2bCustomerAccountGetSubresource(): void
    {
        $docs = $this->getSubresourceEntityDocsForAction('b2bcustomers', 'account', ApiAction::GET_SUBRESOURCE);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals('Get account', $resourceData['description']);
        self::assertEquals(
            '<p>Retrieve the account record associated with a specific business customer record.</p>',
            $resourceData['documentation']
        );
    }

    public function testB2bCustomerAccountGetRelationship(): void
    {
        $docs = $this->getSubresourceEntityDocsForAction('b2bcustomers', 'account', ApiAction::GET_RELATIONSHIP);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals('Get "account" relationship', $resourceData['description']);
        self::assertEquals(
            '<p>Retrieve the ID of the account associated with a specific business customer record.</p>',
            $resourceData['documentation']
        );
    }

    public function testAccountB2bCustomersGetSubresource(): void
    {
        $docs = $this->getSubresourceEntityDocsForAction('accounts', 'b2bCustomers', ApiAction::GET_SUBRESOURCE);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals('Get b2b customers', $resourceData['description']);
        self::assertEquals(
            '<p>Retrieve the records of the business customers associated with a specific account record.</p>',
            $resourceData['documentation']
        );
    }

    public function testAccountB2bCustomersGetRelationship(): void
    {
        $docs = $this->getSubresourceEntityDocsForAction('accounts', 'b2bCustomers', ApiAction::GET_RELATIONSHIP);
        $resourceData = $this->getResourceData($this->getSimpleFormatter()->format($docs));
        self::assertEquals('Get "b2b customers" relationship', $resourceData['description']);
        self::assertEquals(
            '<p>Retrieve the IDs of the business customers associated with a specific account record.</p>',
            $resourceData['documentation']
        );
    }
}
