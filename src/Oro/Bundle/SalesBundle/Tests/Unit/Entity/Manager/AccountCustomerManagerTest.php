<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity\Manager;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation\AccountProviderInterface;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub as Customer;
use Oro\Bundle\SalesBundle\Tests\Unit\Stub\AccountCustomerManager;

class AccountCustomerManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccountCustomerManager */
    private $manager;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var AccountProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $accountProvider;

    /** @var CustomerRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $customerRepo;

    protected function setUp(): void
    {
        $this->customerRepo = $this->createMock(CustomerRepository::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->accountProvider = $this->getMockForAbstractClass(AccountProviderInterface::class);

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->willReturn($this->customerRepo);

        $this->manager = new AccountCustomerManager(
            $this->doctrineHelper,
            $this->configProvider,
            $this->accountProvider
        );
    }

    public function testGetCustomerTargetField()
    {
        $targetClassName = 'TestClass';
        $expected = ExtendHelper::buildAssociationName(
            $targetClassName,
            CustomerScope::ASSOCIATION_KIND
        );
        $this->assertEquals($expected, AccountCustomerManager::getCustomerTargetField($targetClassName));
    }

    public function testCreateCustomerFromAccount()
    {
        $account = (new Account())->setName('test');
        $customer = $this->manager->createCustomer($account);
        $this->assertEquals($account, $customer->getAccount());
    }

    public function testGetAccountCustomerByTargetIfTargetIsAccount()
    {
        $target = (new Account())->setName('test');
        $this->configProvider->expects($this->once())
            ->method('getCustomerClasses')
            ->willReturn(['TestClass']);

        $customer = $this->manager->getAccountCustomerByTarget($target);
        $this->assertEquals($target, $customer->getAccount());
    }

    public function testGetOrCreateAccountCustomerByExistedTarget()
    {
        $target = new Customer();
        $targetField = AccountCustomerManager::getCustomerTargetField(Customer::class);
        $existedCustomer = new Customer();
        $this->configProvider->expects($this->once())
            ->method('getCustomerClasses')
            ->willReturn([Customer::class]);
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($target)
            ->willReturn(1);
        $this->customerRepo->expects($this->once())
            ->method('findOneBy')
            ->with([$targetField => 1])
            ->willReturn($existedCustomer);

        $customer = $this->manager->getAccountCustomerByTarget($target);

        $this->assertEquals($existedCustomer, $customer);
    }
}
