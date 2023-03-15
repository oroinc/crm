<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity\Manager;

use Doctrine\ORM\EntityNotFoundException;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Factory\CustomerFactory;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Exception\Customer\InvalidCustomerRelationEntityException;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation\AccountProviderInterface;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub as TargetEntity;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class AccountCustomerManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var AccountProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $accountProvider;

    /** @var AccountCustomerManager */
    private $manager;

    /** @var CustomerFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $customerFactory;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->accountProvider = $this->createMock(AccountProviderInterface::class);
        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
            ->onlyMethods(['createCustomer'])
            ->getMock();

        $this->manager = new AccountCustomerManager(
            $this->doctrineHelper,
            $this->configProvider,
            $this->accountProvider,
            $this->customerFactory
        );
    }

    public function testGetCustomerTargetField(): void
    {
        $targetClassName = TargetEntity::class;
        $expected = ExtendHelper::buildAssociationName(
            $targetClassName,
            CustomerScope::ASSOCIATION_KIND
        );
        self::assertEquals($expected, AccountCustomerManager::getCustomerTargetField($targetClassName));
    }

    public function testCreateAccountForTarget(): void
    {
        $target = new TargetEntity();
        $account = $this->createMock(Account::class);

        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn([TargetEntity::class]);
        $this->accountProvider->expects(self::once())
            ->method('getAccount')
            ->with(self::identicalTo($target))
            ->willReturn($account);

        self::assertSame($account, $this->manager->createAccountForTarget($target));
    }

    public function testCreateAccountForTargetWhenTargetIsNotSupported(): void
    {
        $this->expectException(InvalidCustomerRelationEntityException::class);
        $this->expectExceptionMessage('object of class "stdClass" is not valid customer target');

        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn([TargetEntity::class]);

        $this->manager->createAccountForTarget(new \stdClass());
    }

    public function testGetAccountCustomerByTargetWhenTargetIsNewAccount(): void
    {
        $target = $this->createMock(Account::class);

        $this->doctrineHelper->expects(self::once())
            ->method('isNewEntity')
            ->with(self::identicalTo($target))
            ->willReturn(true);
        $this->configProvider->expects(self::never())
            ->method('getCustomerClasses');
        $this->doctrineHelper->expects(self::never())
            ->method('getEntityRepository');
        $customerMock = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['setTarget'])
            ->getMock();
        $customerMock->expects($this->never())
            ->method('setTarget');

        $customer = $this->manager->getAccountCustomerByTarget($target);
        self::assertNull($customer->getId());
    }

    public function testGetAccountCustomerByTargetWhenTargetIsExistingAccountAndAssociationDoesNotExist(): void
    {
        $target = $this->createMock(Account::class);

        $this->doctrineHelper->expects(self::once())
            ->method('isNewEntity')
            ->with(self::identicalTo($target))
            ->willReturn(false);
        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn([TargetEntity::class]);

        $customerRepository = $this->createMock(CustomerRepository::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityRepository')
            ->with(Customer::class)
            ->willReturn($customerRepository);
        $customerRepository->expects(self::once())
            ->method('getAccountCustomer')
            ->with(
                self::identicalTo($target),
                [AccountCustomerManager::getCustomerTargetField(TargetEntity::class)]
            )
            ->willReturn(null);
        $customerMock = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['setTarget'])
            ->getMock();
        $customerMock->expects($this->once())
            ->method('setTarget')
            ->with($target, null);
        $this->customerFactory->expects($this->once())
            ->method('createCustomer')
            ->willReturn($customerMock);

        $customer = $this->manager->getAccountCustomerByTarget($target);
        self::assertNull($customer->getId());
    }

    public function testGetAccountCustomerByTargetWhenTargetIsExistingAccountAndAssociationExists(): void
    {
        $target = $this->createMock(Account::class);
        $existingCustomer = new Customer();

        $this->doctrineHelper->expects(self::once())
            ->method('isNewEntity')
            ->with(self::identicalTo($target))
            ->willReturn(false);
        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn([TargetEntity::class]);

        $customerRepository = $this->createMock(CustomerRepository::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityRepository')
            ->with(Customer::class)
            ->willReturn($customerRepository);
        $customerRepository->expects(self::once())
            ->method('getAccountCustomer')
            ->with(
                self::identicalTo($target),
                [AccountCustomerManager::getCustomerTargetField(TargetEntity::class)]
            )
            ->willReturn($existingCustomer);

        self::assertSame($existingCustomer, $this->manager->getAccountCustomerByTarget($target));
    }

    public function testGetAccountCustomerByTargetForExistingTargetAndAssociationDoesNotExist(): void
    {
        $target = new TargetEntity();
        $targetId = 123;

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Sales Customer for target of type "%s" and identifier %d was not found',
            get_class($target),
            $targetId
        ));

        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn([TargetEntity::class]);
        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->with(self::identicalTo($target))
            ->willReturn($targetId);

        $customerRepository = $this->createMock(CustomerRepository::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityRepository')
            ->with(Customer::class)
            ->willReturn($customerRepository);
        $customerRepository->expects(self::once())
            ->method('findOneBy')
            ->with([AccountCustomerManager::getCustomerTargetField(TargetEntity::class) => $targetId])
            ->willReturn(null);

        $this->manager->getAccountCustomerByTarget($target);
    }

    public function testGetAccountCustomerByTargetForExistingTargetAndAssociationDoesNotExistAndNoException(): void
    {
        $target = new TargetEntity();
        $targetId = 123;

        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn([TargetEntity::class]);
        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->with(self::identicalTo($target))
            ->willReturn($targetId);

        $customerRepository = $this->createMock(CustomerRepository::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityRepository')
            ->with(Customer::class)
            ->willReturn($customerRepository);
        $customerRepository->expects(self::once())
            ->method('findOneBy')
            ->with([AccountCustomerManager::getCustomerTargetField(TargetEntity::class) => $targetId])
            ->willReturn(null);

        self::assertNull($this->manager->getAccountCustomerByTarget($target, false));
    }

    public function testGetAccountCustomerByTargetForExistingTargetAndAssociationExists(): void
    {
        $target = new TargetEntity();
        $targetId = 123;
        $existingCustomer = new Customer();

        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn([TargetEntity::class]);
        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->with(self::identicalTo($target))
            ->willReturn($targetId);

        $customerRepository = $this->createMock(CustomerRepository::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityRepository')
            ->with(Customer::class)
            ->willReturn($customerRepository);
        $customerRepository->expects(self::once())
            ->method('findOneBy')
            ->with([AccountCustomerManager::getCustomerTargetField(TargetEntity::class) => $targetId])
            ->willReturn($existingCustomer);

        self::assertSame($existingCustomer, $this->manager->getAccountCustomerByTarget($target));
    }

    public function testGetAccountCustomerByTargetForNewTarget(): void
    {
        $target = new TargetEntity();

        $this->expectException(EntityNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Sales Customer for target of type "%s" and identifier %s was not found',
            get_class($target),
            ''
        ));

        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn([TargetEntity::class]);
        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->with(self::identicalTo($target))
            ->willReturn(null);

        $this->doctrineHelper->expects(self::never())
            ->method('getEntityRepository');

        $this->manager->getAccountCustomerByTarget($target);
    }

    public function testGetAccountCustomerByTargetForNewTargetAndNoException(): void
    {
        $target = new TargetEntity();

        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn([TargetEntity::class]);
        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->with(self::identicalTo($target))
            ->willReturn(null);

        $this->doctrineHelper->expects(self::never())
            ->method('getEntityRepository');

        self::assertNull($this->manager->getAccountCustomerByTarget($target, false));
    }
}
