<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\DataTransformer;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityExtendBundle\Test\ExtendedEntityTestTrait;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Form\DataTransformer\CustomerToStringTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CustomerToStringTransformerTest extends \PHPUnit\Framework\TestCase
{
    use ExtendedEntityTestTrait;

    /** @var DataTransformerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityToStringTransformer;

    /** @var AccountCustomerManager|\PHPUnit\Framework\MockObject\MockObject */
    private $accountCustomerManager;

    /** @var CustomerToStringTransformer */
    private $transformer;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityToStringTransformer = $this->createMock(DataTransformerInterface::class);
        $this->accountCustomerManager = $this->createMock(AccountCustomerManager::class);

        $this->transformer = new CustomerToStringTransformer(
            $this->entityToStringTransformer,
            $this->accountCustomerManager
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

        $this->entityFieldTestExtension->addExpectation(
            Customer::class,
            'setCustomerTarget',
            function (array $arguments) {
                self::assertSame([null], $arguments);

                return true;
            }
        );

        $this->entityToStringTransformer->expects(self::never())
            ->method('reverseTransform');
        $this->accountCustomerManager->expects(self::never())
            ->method('getAccountCustomerByTarget');

        $result = $this->transformer->reverseTransform($value);
        self::assertInstanceOf(Customer::class, $result);
        self::assertInstanceOf(Account::class, $result->getAccount());
        self::assertEquals($accountName, $result->getAccount()->getName());
    }

    public function testReverseTransformForExistingAccount(): void
    {
        $value = json_encode(['entityClass' => Account::class, 'entityId' => 123], JSON_THROW_ON_ERROR);

        $account = new Account();
        $customer = new Customer();

        $this->entityToStringTransformer->expects(self::once())
            ->method('reverseTransform')
            ->with($value)
            ->willReturn($account);
        $this->accountCustomerManager->expects(self::once())
            ->method('getAccountCustomerByTarget')
            ->with(self::identicalTo($account))
            ->willReturn($customer);

        self::assertSame($customer, $this->transformer->reverseTransform($value));
    }

    public function testTransformForCustomerEntity(): void
    {
        $account = new Account();
        $customer = $this->getMockBuilder(Customer::class)
            ->addMethods(['getCustomerTarget'])
            ->getMock();
        $customer->expects(self::once())
            ->method('getCustomerTarget')
            ->willReturn($account);

        $transformedValue = 'transformedValue';

        $this->entityToStringTransformer->expects(self::once())
            ->method('transform')
            ->with(self::identicalTo($account))
            ->willReturn($transformedValue);

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
