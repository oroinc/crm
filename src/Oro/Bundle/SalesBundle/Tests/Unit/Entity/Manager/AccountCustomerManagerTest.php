<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity\Manager;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\AccountAwareCustomerTarget;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub;

class AccountCustomerManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AccountCustomerManager */
    protected $manager;

    /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $doctrineHelper;

    /** @var ConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $configProvider;

    /** @var EntityNameResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $nameResolver;

    /** @var CustomerRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepo;

    protected function setUp()
    {
        $this->customerRepo   = $this
            ->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper = $this
            ->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->willReturn($this->customerRepo);
        $this->configProvider = $this
            ->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->nameResolver   = $this
            ->getMockBuilder(EntityNameResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new AccountCustomerManager(
            $this->doctrineHelper,
            $this->configProvider,
            $this->nameResolver
        );
    }

    public function testGetCustomerTargetField()
    {
        $targetClassName = 'TestClass';
        $expected        = ExtendHelper::buildAssociationName(
            $targetClassName,
            CustomerScope::ASSOCIATION_KIND
        );
        $this->assertEquals($expected, AccountCustomerManager::getCustomerTargetField($targetClassName));
    }

    public function testCreateCustomerFromAccount()
    {
        $account  = (new Account())->setName('test');
        $customer = $this->manager->createCustomerFromAccount($account);
        $this->assertEquals($account, $customer->getAccount());
    }

    public function testGetOrCreateAccountCustomerByTargetTargetIsAccount()
    {
        $target = (new Account())->setName('test');
        $this->configProvider
            ->expects($this->once())
            ->method('getCustomerClasses')
            ->willReturn(['TestClass']);

        $customer = $this->manager->getOrCreateAccountCustomerByTarget($target);
        $this->assertEquals($target, $customer->getAccount());
    }

    public function testGetOrCreateAccountCustomerByExistedTarget()
    {
        $target          = new CustomerStub();
        $targetField     = AccountCustomerManager::getCustomerTargetField(CustomerStub::class);
        $existedCustomer = new Customer();
        $this->configProvider
            ->expects($this->once())
            ->method('getCustomerClasses')
            ->willReturn([CustomerStub::class]);
        $this->doctrineHelper
            ->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($target)
            ->willReturn(1);
        $this->customerRepo
            ->expects($this->once())
            ->method('findOneBy')
            ->with([$targetField => 1])
            ->willReturn($existedCustomer);

        $customer = $this->manager->getOrCreateAccountCustomerByTarget($target);

        $this->assertEquals($existedCustomer, $customer);
    }

    /**
     * @expectedException \Oro\Bundle\SalesBundle\Exception\Customer\NotAccessableCustomerTargetException
     * @expectedExceptionMessage Couldn't sync Customer's target account without target
     */
    public function testSyncTargetCustomerAccountWithoutTarget()
    {
        $customer = new CustomerStub();
        $this->manager->syncTargetCustomerAccount($customer);
    }

    public function testSyncTargetCustomerAccountTargetWithAccount()
    {
        $account = new Account();
        $customer = new CustomerStub();
        $target = new AccountAwareCustomerTarget(1, $account);
        $customer->setCustomerTarget($target);
        $this->manager->syncTargetCustomerAccount($customer);
        $this->assertEquals($customer->getAccount(), $target->getAccount());
    }

    public function testSyncTargetCustomerAccountTargetWithNoAccount()
    {
        $customer = new CustomerStub();
        $target = new AccountAwareCustomerTarget(1);
        $customer->setCustomerTarget($target);
        $this->manager->syncTargetCustomerAccount($customer);
        $this->assertInstanceOf(Account::class, $customer->getAccount());
    }

    public function testSyncTargetCustomerAccountTargetNotAccountAware()
    {
        $customer = new CustomerStub();
        $target = new \stdClass;
        $customer->setCustomerTarget($target);
        $this->manager->syncTargetCustomerAccount($customer);
        $this->assertInstanceOf(Account::class, $customer->getAccount());
    }
}
