<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\DataTransformer;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Factory\CustomerFactory;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Form\DataTransformer\CustomerToStringTransformer;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CustomerToStringTransformerTest extends \PHPUnit\Framework\TestCase
{
    /** @var DataTransformerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityToStringTransformer;

    /** @var AccountCustomerManager|\PHPUnit\Framework\MockObject\MockObject */
    private $accountCustomerManager;

    /** @var CustomerToStringTransformer */
    private $transformer;

    /** @var CustomerFactory|\PHPUnit\Framework\MockObject\MockObject  */
    private $customerFactory;

    protected function setUp(): void
    {
        $this->entityToStringTransformer = $this->createMock(DataTransformerInterface::class);
        $this->accountCustomerManager = $this->createMock(AccountCustomerManager::class);
        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
            ->onlyMethods(['createCustomer'])
            ->getMock();

        $this->transformer = new CustomerToStringTransformer(
            $this->entityToStringTransformer,
            $this->accountCustomerManager,
            $this->customerFactory
        );
    }

    public function testReverseTransformForNull(): void
    {
        $this->entityToStringTransformer->expects(self::never())
            ->method('reverseTransform');
        $this->accountCustomerManager->expects(self::never())
            ->method('getAccountCustomerByTarget');

        self::assertNull($this->transformer->reverseTransform(null));
    }

    public function testReverseTransformForEmptyString(): void
    {
        $this->entityToStringTransformer->expects(self::never())
            ->method('reverseTransform');
        $this->accountCustomerManager->expects(self::never())
            ->method('getAccountCustomerByTarget');

        self::assertNull($this->transformer->reverseTransform(''));
    }

    public function testReverseTransformForNonString(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected a string.');

        $this->transformer->reverseTransform(123);
    }

    public function testReverseTransformForInvalidValue(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected an array after decoding a string.');

        $this->transformer->reverseTransform(json_encode('test', JSON_THROW_ON_ERROR));
    }

    public function testReverseTransformForNewAccount(): void
    {
        $accountName = 'new account';
        $value = json_encode(['value' => $accountName], JSON_THROW_ON_ERROR);

        $account = new Account();
        $account->setName($accountName);
        $customer = $this->getMockBuilder(Customer::class)->addMethods(['setCustomerTarget'])->getMock();
        $this->customerFactory->expects($this->once())->method('createCustomer')->willReturn($customer);
        $this->entityToStringTransformer->expects(self::never())
            ->method('reverseTransform');
        $this->accountCustomerManager->expects(self::never())
            ->method('getAccountCustomerByTarget');

        self::assertEquals($customer, $this->transformer->reverseTransform($value));
    }

    public function testReverseTransformForExistingAccount(): void
    {
        $accountId = 123;
        $value = json_encode(['entityClass' => Account::class, 'entityId' => $accountId], JSON_THROW_ON_ERROR);

        $account = new Account();
        ReflectionUtil::setId($account, $accountId);
        $customer = $this->getMockBuilder(Customer::class)->onlyMethods(['setTarget'])->getMock();
        $this->customerFactory->expects($this->never())->method('createCustomer');

        $this->entityToStringTransformer->expects(self::once())
            ->method('reverseTransform')
            ->willReturn($account);
        $this->accountCustomerManager->expects(self::once())
            ->method('getAccountCustomerByTarget')
            ->with(self::identicalTo($account))
            ->willReturn($customer);

        /** @var Customer $result */
        $result = $this->transformer->reverseTransform($value);
        self::assertSame($customer, $result);
    }

    public function testTransformForCustomerEntity(): void
    {
        $account = new Account();
        $customer = $this->getMockBuilder(Customer::class)
            ->onlyMethods(['getTarget'])
            ->addMethods(['getCustomerTarget'])
            ->getMock();
        $transformedValue = 'transformedValue';

        $this->entityToStringTransformer->expects(self::once())
            ->method('transform')
            ->with(self::identicalTo($account))
            ->willReturn($transformedValue);
        $customer->expects($this->once())->method('getTarget')->willReturn($account);

        self::assertEquals($transformedValue, $this->transformer->transform($customer));
    }

    public function testTransformForNotCustomerEntity(): void
    {
        $account = new Account();
        $transformedValue = 'transformedValue';

        $this->entityToStringTransformer->expects(self::once())
            ->method('transform')
            ->with(self::identicalTo($account))
            ->willReturn($transformedValue);

        self::assertEquals($transformedValue, $this->transformer->transform($account));
    }
}
