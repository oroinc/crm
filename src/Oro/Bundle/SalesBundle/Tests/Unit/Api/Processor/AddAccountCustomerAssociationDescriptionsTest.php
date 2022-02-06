<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\ApiDoc\EntityNameProvider;
use Oro\Bundle\ApiBundle\ApiDoc\ResourceDocParserInterface;
use Oro\Bundle\ApiBundle\Processor\GetConfig\CompleteDescriptions\ResourceDocParserProvider;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\DataType;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ApiBundle\Tests\Unit\Processor\GetConfig\ConfigProcessorTestCase;
use Oro\Bundle\SalesBundle\Api\AccountCustomerAssociationProvider;
use Oro\Bundle\SalesBundle\Api\Processor\AddAccountCustomerAssociationDescriptions;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AddAccountCustomerAssociationDescriptionsTest extends ConfigProcessorTestCase
{
    /** @var AccountCustomerAssociationProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $accountCustomerAssociationProvider;

    /** @var ResourceDocParserInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $docParser;

    /** @var AddAccountCustomerAssociationDescriptions */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountCustomerAssociationProvider = $this->createMock(AccountCustomerAssociationProvider::class);
        $this->docParser = $this->createMock(ResourceDocParserInterface::class);

        $resourceDocParserProvider = $this->createMock(ResourceDocParserProvider::class);
        $resourceDocParserProvider->expects(self::any())
            ->method('getResourceDocParser')
            ->with($this->context->getRequestType())
            ->willReturn($this->docParser);

        $entityNameProvider = $this->createMock(EntityNameProvider::class);
        $entityNameProvider->expects(self::any())
            ->method('getEntityName')
            ->with(self::isType('string'))
            ->willReturnCallback(function (string $className) {
                return strtolower(substr($className, strrpos($className, '\\') + 1)) . ' (description)';
            });
        $entityNameProvider->expects(self::any())
            ->method('getEntityPluralName')
            ->with(self::isType('string'))
            ->willReturnCallback(function (string $className) {
                return strtolower(substr($className, strrpos($className, '\\') + 1)) . ' (plural description)';
            });

        $valueNormalizer = $this->createMock(ValueNormalizer::class);
        $valueNormalizer->expects(self::any())
            ->method('normalizeValue')
            ->with(self::isType('string'), DataType::ENTITY_TYPE, $this->context->getRequestType())
            ->willReturnCallback(function (string $className) {
                return strtolower(substr($className, strrpos($className, '\\') + 1));
            });

        $this->processor = new AddAccountCustomerAssociationDescriptions(
            $this->accountCustomerAssociationProvider,
            $valueNormalizer,
            $resourceDocParserProvider,
            $entityNameProvider
        );
    }

    public function testProcessWhenNoTargetAction(): void
    {
        $entityClass = 'Test\Entity';
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('isCustomerEntity');
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);

        $this->assertConfig([], $definition);
    }

    public function testProcessWhenTargetActionIsOptions(): void
    {
        $entityClass = 'Test\Entity';
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('isCustomerEntity');
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->context->setTargetAction(ApiAction::OPTIONS);
        $this->processor->process($this->context);

        $this->assertConfig([], $definition);
    }

    public function testProcessForCustomerEntity(): void
    {
        $entityClass = 'Test\Customer';
        $targetAction = ApiAction::UPDATE;
        $definition = $this->createConfigObject([
            'fields' => [
                'account' => [
                    'target_class' => Account::class
                ]
            ]
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->docParser->expects(self::once())
            ->method('registerDocumentationResource')
            ->with('@OroSalesBundle/Resources/doc/api/account_association.md');
        $this->docParser->expects(self::once())
            ->method('getFieldDocumentation')
            ->with('%customer_entity%', '%account_association%', $targetAction)
            ->willReturn('Description for "%customer_entity_name%".');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'account' => [
                        'target_class' => Account::class,
                        'description'  => 'Description for "customer (description)".'
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForCustomerEntityWhenNoDocumentationForTargetAction(): void
    {
        $entityClass = 'Test\Customer';
        $targetAction = ApiAction::UPDATE;
        $definition = $this->createConfigObject([
            'fields' => [
                'account' => [
                    'target_class' => Account::class
                ]
            ]
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->docParser->expects(self::once())
            ->method('registerDocumentationResource')
            ->with('@OroSalesBundle/Resources/doc/api/account_association.md');
        $this->docParser->expects(self::exactly(2))
            ->method('getFieldDocumentation')
            ->willReturnMap([
                ['%customer_entity%', '%account_association%', $targetAction, null],
                ['%customer_entity%', '%account_association%', null, 'Description for "%customer_entity_name%".']
            ]);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'account' => [
                        'target_class' => Account::class,
                        'description'  => 'Description for "customer (description)".'
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForCustomerEntityWhenAccountAssociationAlreadyHasDescription(): void
    {
        $entityClass = 'Test\Customer';
        $targetAction = ApiAction::UPDATE;
        $definition = $this->createConfigObject([
            'fields' => [
                'account' => [
                    'description' => 'Existing description.'
                ]
            ]
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->docParser->expects(self::never())
            ->method('registerDocumentationResource');
        $this->docParser->expects(self::never())
            ->method('getFieldDocumentation');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'account' => [
                        'description' => 'Existing description.'
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForCustomerEntityWhenAccountAssociationDoesNotExist(): void
    {
        $entityClass = 'Test\Customer';
        $targetAction = ApiAction::UPDATE;
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->docParser->expects(self::never())
            ->method('registerDocumentationResource');
        $this->docParser->expects(self::never())
            ->method('getFieldDocumentation');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig([], $definition);
    }

    public function testProcessForAccountWithCustomerAssociations(): void
    {
        $entityClass = Account::class;
        $targetAction = ApiAction::UPDATE;
        $definition = $this->createConfigObject([
            'fields' => [
                'customers' => [
                    'target_class' => 'Test\Customer'
                ]
            ]
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(false);
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers' => ['className' => 'Test\Customer', 'associationName' => 'association1']
            ]);

        $this->docParser->expects(self::once())
            ->method('registerDocumentationResource')
            ->with('@OroSalesBundle/Resources/doc/api/account_customers_association.md');
        $this->docParser->expects(self::once())
            ->method('getFieldDocumentation')
            ->with(Account::class, '%customers_association%', $targetAction)
            ->willReturn('Description for "%customer_entity_plural_name%" associated with the account.');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'customers' => [
                        'target_class' => 'Test\Customer',
                        'description'  => 'Description for "customer (plural description)"'
                            . ' associated with the account.'
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForAccountWithCustomerAssociationsWhenNoDocumentationForTargetAction(): void
    {
        $entityClass = Account::class;
        $targetAction = ApiAction::UPDATE;
        $definition = $this->createConfigObject([
            'fields' => [
                'customers' => [
                    'target_class' => 'Test\Customer'
                ]
            ]
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(false);
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers' => ['className' => 'Test\Customer', 'associationName' => 'association1']
            ]);

        $this->docParser->expects(self::once())
            ->method('registerDocumentationResource')
            ->with('@OroSalesBundle/Resources/doc/api/account_customers_association.md');
        $this->docParser->expects(self::exactly(2))
            ->method('getFieldDocumentation')
            ->willReturnMap([
                [Account::class, '%customers_association%', $targetAction, null],
                [
                    Account::class,
                    '%customers_association%',
                    null,
                    'Description for "%customer_entity_plural_name%" associated with the account.'
                ]
            ]);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'customers' => [
                        'target_class' => 'Test\Customer',
                        'description'  => 'Description for "customer (plural description)"'
                            . ' associated with the account.'
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForAccountWithCustomerAssociationsWhenAssociationAlreadyHasDescription(): void
    {
        $entityClass = Account::class;
        $targetAction = ApiAction::UPDATE;
        $definition = $this->createConfigObject([
            'fields' => [
                'customers' => [
                    'target_class' => 'Test\Customer',
                    'description'  => 'Existing description.'
                ]
            ]
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(false);
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers' => ['className' => 'Test\Customer', 'associationName' => 'association1']
            ]);

        $this->docParser->expects(self::once())
            ->method('registerDocumentationResource')
            ->with('@OroSalesBundle/Resources/doc/api/account_customers_association.md');
        $this->docParser->expects(self::once())
            ->method('getFieldDocumentation')
            ->with(Account::class, '%customers_association%', $targetAction)
            ->willReturn('Description for "%customer_entity_plural_name%" associated with the account.');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'customers' => [
                        'target_class' => 'Test\Customer',
                        'description'  => 'Existing description.'
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForAccountWithCustomerAssociationsWhenAssociationDoesNotExist(): void
    {
        $entityClass = Account::class;
        $targetAction = ApiAction::UPDATE;
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(false);
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers' => ['className' => 'Test\Customer', 'associationName' => 'association1']
            ]);

        $this->docParser->expects(self::once())
            ->method('registerDocumentationResource')
            ->with('@OroSalesBundle/Resources/doc/api/account_customers_association.md');
        $this->docParser->expects(self::once())
            ->method('getFieldDocumentation')
            ->with(Account::class, '%customers_association%', $targetAction)
            ->willReturn('Description for "%customer_entity_plural_name%" associated with the account.');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig([], $definition);
    }

    public function testProcessSubresourceForCustomerEntity(): void
    {
        $entityClass = Account::class;
        $parentEntityClass = 'Test\Customer';
        $associationName = 'account';
        $targetAction = ApiAction::UPDATE_RELATIONSHIP;
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($parentEntityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->docParser->expects(self::once())
            ->method('registerDocumentationResource')
            ->with('@OroSalesBundle/Resources/doc/api/account_association.md');
        $this->docParser->expects(self::once())
            ->method('getSubresourceDocumentation')
            ->with('%customer_entity%', '%account_association%', $targetAction)
            ->willReturn('Documentation for "%customer_entity_name%".');

        $this->context->setClassName($entityClass);
        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'documentation' => 'Documentation for "customer (description)".'
            ],
            $definition
        );
    }

    public function testProcessSubresourceForCustomerEntityWhenItAlreadyHasDocumentation(): void
    {
        $entityClass = Account::class;
        $parentEntityClass = 'Test\Customer';
        $associationName = 'account';
        $targetAction = ApiAction::UPDATE_RELATIONSHIP;
        $definition = $this->createConfigObject([
            'documentation' => 'Existing documentation.'
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($parentEntityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->docParser->expects(self::never())
            ->method('registerDocumentationResource');
        $this->docParser->expects(self::never())
            ->method('getSubresourceDocumentation');

        $this->context->setClassName($entityClass);
        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'documentation' => 'Existing documentation.'
            ],
            $definition
        );
    }

    public function testProcessSubresourceForCustomerEntityForNotAccountSubresource(): void
    {
        $entityClass = Account::class;
        $parentEntityClass = 'Test\Customer';
        $associationName = 'another';
        $targetAction = ApiAction::UPDATE_RELATIONSHIP;
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($parentEntityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->docParser->expects(self::never())
            ->method('registerDocumentationResource');
        $this->docParser->expects(self::never())
            ->method('getSubresourceDocumentation');

        $this->context->setClassName($entityClass);
        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig([], $definition);
    }

    public function testProcessSubresourceForAccountWithCustomerAssociations(): void
    {
        $entityClass = 'Test\Customer';
        $parentEntityClass = Account::class;
        $associationName = 'customers';
        $targetAction = ApiAction::UPDATE_RELATIONSHIP;
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($parentEntityClass)
            ->willReturn(false);
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers' => ['className' => 'Test\Customer', 'associationName' => 'association1']
            ]);

        $this->docParser->expects(self::once())
            ->method('registerDocumentationResource')
            ->with('@OroSalesBundle/Resources/doc/api/account_customers_association.md');
        $this->docParser->expects(self::once())
            ->method('getSubresourceDocumentation')
            ->with(Account::class, '%customers_association%', $targetAction)
            ->willReturn(
                'Documentation for "%customer_entity_plural_name%" associated with the account'
                . ' (target: "%customer_entity_type%").'
            );

        $this->context->setClassName($entityClass);
        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'documentation' => 'Documentation for "customer (plural description)"'
                    . ' associated with the account (target: "customer").'
            ],
            $definition
        );
    }

    public function testProcessSubresourceForAccountWithCustomerAssociationsWhenItAlreadyHasDocumentation(): void
    {
        $entityClass = 'Test\Customer';
        $parentEntityClass = Account::class;
        $associationName = 'customers';
        $targetAction = ApiAction::UPDATE_RELATIONSHIP;
        $definition = $this->createConfigObject([
            'documentation' => 'Existing documentation.'
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($parentEntityClass)
            ->willReturn(false);
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers' => ['className' => 'Test\Customer', 'associationName' => 'association1']
            ]);

        $this->docParser->expects(self::never())
            ->method('registerDocumentationResource');
        $this->docParser->expects(self::never())
            ->method('getSubresourceDocumentation');

        $this->context->setClassName($entityClass);
        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'documentation' => 'Existing documentation.'
            ],
            $definition
        );
    }

    public function testProcessSubresourceForAccountWithCustomerAssociationsForNotCustomerAssociation(): void
    {
        $entityClass = 'Test\Customer';
        $parentEntityClass = Account::class;
        $associationName = 'another';
        $targetAction = ApiAction::UPDATE_RELATIONSHIP;
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($parentEntityClass)
            ->willReturn(false);
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers' => ['className' => 'Test\Customer', 'associationName' => 'association1']
            ]);

        $this->docParser->expects(self::never())
            ->method('registerDocumentationResource');
        $this->docParser->expects(self::never())
            ->method('getSubresourceDocumentation');

        $this->context->setClassName($entityClass);
        $this->context->setParentClassName($parentEntityClass);
        $this->context->setAssociationName($associationName);
        $this->context->setResult($definition);
        $this->context->setTargetAction($targetAction);
        $this->processor->process($this->context);

        $this->assertConfig([], $definition);
    }
}
