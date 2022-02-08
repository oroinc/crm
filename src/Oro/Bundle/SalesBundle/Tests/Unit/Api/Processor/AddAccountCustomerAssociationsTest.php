<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Api\Processor;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Tests\Unit\Processor\GetConfig\ConfigProcessorTestCase;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\SalesBundle\Api\AccountCustomerAssociationProvider;
use Oro\Bundle\SalesBundle\Api\Processor\AddAccountCustomerAssociations;
use Oro\Bundle\SalesBundle\Entity\Customer;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AddAccountCustomerAssociationsTest extends ConfigProcessorTestCase
{
    /** @var AccountCustomerAssociationProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $accountCustomerAssociationProvider;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var AddAccountCustomerAssociations */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountCustomerAssociationProvider = $this->createMock(AccountCustomerAssociationProvider::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->processor = new AddAccountCustomerAssociations(
            $this->accountCustomerAssociationProvider,
            $this->doctrineHelper
        );
    }

    private function getAccountAssociationQuery(string $customerAssociationName): QueryBuilder
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects(self::exactly(2))
            ->method('innerJoin')
            ->withConsecutive(
                [Customer::class, 'ca', Join::WITH, 'ca.account = r'],
                ['ca.' . $customerAssociationName, 'e']
            )
            ->willReturnSelf();

        return $qb;
    }

    private function getCustomerAssociationQuery(string $customerAssociationName): QueryBuilder
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects(self::exactly(2))
            ->method('innerJoin')
            ->withConsecutive(
                [Customer::class, 'ca', Join::WITH, sprintf('ca.%s = r', $customerAssociationName)],
                ['ca.account', 'e']
            )
            ->willReturnSelf();

        return $qb;
    }

    public function testProcessForCustomer(): void
    {
        $entityClass = 'Test\Customer';
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getCustomerTargetAssociationName')
            ->with($entityClass)
            ->willReturn('association1');

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('hasAssociation')
            ->with('account')
            ->willReturn(false);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityMetadataForClass')
            ->with($entityClass)
            ->willReturn($metadata);

        $qb = $this->getAccountAssociationQuery('association1');
        $this->doctrineHelper->expects(self::once())
            ->method('createQueryBuilder')
            ->with(Account::class, 'r')
            ->willReturn($qb);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'account' => [
                        'target_class'      => Account::class,
                        'target_type'       => 'to-one',
                        'property_path'     => '_',
                        'association_query' => $qb
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForCustomerEntityWhenAccountAssociationIsDisabled(): void
    {
        $entityClass = 'Test\Customer';
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getCustomerTargetAssociationName')
            ->with($entityClass)
            ->willReturn('association1');

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('hasAssociation')
            ->with('account')
            ->willReturn(false);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityMetadataForClass')
            ->with($entityClass)
            ->willReturn($metadata);

        $qb = $this->getAccountAssociationQuery('association1');
        $this->doctrineHelper->expects(self::once())
            ->method('createQueryBuilder')
            ->with(Account::class, 'r')
            ->willReturn($qb);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'account' => [
                        'target_class'      => Account::class,
                        'target_type'       => 'to-one',
                        'property_path'     => '_',
                        'association_query' => $qb
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForCustomerEntityWhenAccountConfiguredManually(): void
    {
        $entityClass = 'Test\Customer';
        $definition = $this->createConfigObject([
            'fields' => [
                'account' => [
                    'exclude' => true
                ]
            ]
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getCustomerTargetAssociationName');

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('hasAssociation')
            ->with('account')
            ->willReturn(false);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityMetadataForClass')
            ->with($entityClass)
            ->willReturn($metadata);

        $this->doctrineHelper->expects(self::never())
            ->method('createQueryBuilder');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'account' => [
                        'target_class'  => Account::class,
                        'target_type'   => 'to-one',
                        'property_path' => '_',
                        'exclude'       => true
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForCustomerEntityWhenAccountAssociationReconfiguredToUseAnotherTargetEntity(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'The association "account" cannot be added to "Test\Customer"'
            . ' because an association with this name already exists.'
        );

        $entityClass = 'Test\Customer';
        $definition = $this->createConfigObject([
            'fields' => [
                'account' => [
                    'targetClass' => 'Test\AnotherAccount'
                ]
            ]
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);
    }

    public function testProcessForCustomerEntityThatHasAccountAssociation(): void
    {
        $entityClass = 'Test\Customer';
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(true);
        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::once())
            ->method('hasAssociation')
            ->with('account')
            ->willReturn(true);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityMetadataForClass')
            ->with($entityClass)
            ->willReturn($metadata);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);

        $this->assertConfig([], $definition);
    }

    public function testProcessForAccountWithoutCustomerAssociations(): void
    {
        $entityClass = Account::class;
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(false);
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([]);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);

        $this->assertConfig([], $definition);
    }

    public function testProcessForAccountWithCustomerAssociations(): void
    {
        $entityClass = Account::class;
        $definition = $this->createConfigObject([]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('isCustomerEntity')
            ->with($entityClass)
            ->willReturn(false);
        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers'     => ['className' => 'Test\Customer', 'associationName' => 'association1'],
                'acmeCustomers' => ['className' => 'Test\AcmeCustomer', 'associationName' => 'association2']
            ]);

        $qb1 = $this->getCustomerAssociationQuery('association1');
        $qb2 = $this->getCustomerAssociationQuery('association2');
        $this->doctrineHelper->expects(self::exactly(2))
            ->method('createQueryBuilder')
            ->willReturnMap([
                ['Test\Customer', 'r', null, $qb1],
                ['Test\AcmeCustomer', 'r', null, $qb2]
            ]);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'customers'     => [
                        'target_class'      => 'Test\Customer',
                        'target_type'       => 'to-many',
                        'property_path'     => '_',
                        'association_query' => $qb1
                    ],
                    'acmeCustomers' => [
                        'target_class'      => 'Test\AcmeCustomer',
                        'target_type'       => 'to-many',
                        'property_path'     => '_',
                        'association_query' => $qb2
                    ]
                ]
            ],
            $definition
        );
        self::assertSame($qb1, $definition->getField('customers')->getAssociationQuery());
        self::assertSame($qb2, $definition->getField('acmeCustomers')->getAssociationQuery());
    }

    public function testProcessForAccountWithCustomerAssociationsWhenSomeAssociationsAreDisabled(): void
    {
        $entityClass = Account::class;
        $definition = $this->createConfigObject([
            'fields' => [
                'customers' => [
                    'exclude' => true
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
                'customers'     => ['className' => 'Test\Customer', 'associationName' => 'association1'],
                'acmeCustomers' => ['className' => 'Test\AcmeCustomer', 'associationName' => 'association2']
            ]);

        $qb2 = $this->getCustomerAssociationQuery('association2');
        $this->doctrineHelper->expects(self::once())
            ->method('createQueryBuilder')
            ->with('Test\AcmeCustomer', 'r')
            ->willReturn($qb2);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'customers'     => [
                        'target_class'  => 'Test\Customer',
                        'target_type'   => 'to-many',
                        'property_path' => '_',
                        'exclude'       => true
                    ],
                    'acmeCustomers' => [
                        'target_class'      => 'Test\AcmeCustomer',
                        'target_type'       => 'to-many',
                        'property_path'     => '_',
                        'association_query' => $qb2
                    ]
                ]
            ],
            $definition
        );
    }

    public function testProcessForAccountWithCustomerAssociationsWhenSomeAssociationsConfiguredManually(): void
    {
        $entityClass = Account::class;
        /** @var EntityDefinitionConfig $definition */
        $definition = $this->createConfigObject([
            'fields' => [
                'customers' => [
                    'target_class'  => 'Test\Customer',
                    'target_type'   => 'to-many',
                    'property_path' => '_'
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
                'customers'     => ['className' => 'Test\Customer', 'associationName' => 'association1'],
                'acmeCustomers' => ['className' => 'Test\AcmeCustomer', 'associationName' => 'association2']
            ]);

        $qb1 = $this->getCustomerAssociationQuery('association1');
        $qb2 = $this->getCustomerAssociationQuery('association2');
        $this->doctrineHelper->expects(self::exactly(2))
            ->method('createQueryBuilder')
            ->willReturnMap([
                ['Test\Customer', 'r', null, $qb1],
                ['Test\AcmeCustomer', 'r', null, $qb2]
            ]);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);

        $this->assertConfig(
            [
                'fields' => [
                    'customers'     => [
                        'target_class'      => 'Test\Customer',
                        'target_type'       => 'to-many',
                        'property_path'     => '_',
                        'association_query' => $qb1
                    ],
                    'acmeCustomers' => [
                        'target_class'      => 'Test\AcmeCustomer',
                        'target_type'       => 'to-many',
                        'property_path'     => '_',
                        'association_query' => $qb2
                    ]
                ]
            ],
            $definition
        );
        self::assertSame($qb1, $definition->getField('customers')->getAssociationQuery());
        self::assertSame($qb2, $definition->getField('acmeCustomers')->getAssociationQuery());
    }

    /**
     * @dataProvider reconfiguredCustomerAssociationDataProvider
     */
    public function testProcessForAccountWithCustomerAssociationsWhenSomeHaveReconfigured(array $config): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'The customer association "customers" cannot be added to "%s"'
            . ' because an association with this name already exists.'
            . ' To rename the association to the "Test\Customer" customer entity'
            . ' use "oro_sales.api.customer_association_names" configuration option.'
            . ' For example:' . "\n"
            . 'oro_sales:' . "\n"
            . '    api:' . "\n"
            . '        customer_association_names:' . "\n"
            . '            \'Test\Customer\': \'newName\'',
            Account::class
        ));

        $entityClass = Account::class;
        $definition = $this->createConfigObject([
            'fields' => [
                'customers' => $config
            ]
        ]);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers'     => ['className' => 'Test\Customer', 'associationName' => 'association1'],
                'acmeCustomers' => ['className' => 'Test\AcmeCustomer', 'associationName' => 'association2']
            ]);

        $this->context->setClassName($entityClass);
        $this->context->setResult($definition);
        $this->processor->process($this->context);
    }

    public function reconfiguredCustomerAssociationDataProvider(): array
    {
        return [
            [['target_class' => 'Test\Customer1']],
            [['target_type' => 'to-one']],
            [['property_path' => 'customers']],
        ];
    }
}
