<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\DataTransformer;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Form\DataTransformer\CustomerToStringTransformer;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub as Customer;
use Oro\Bundle\SalesBundle\Tests\Unit\Stub\AccountCustomerManager;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Form\DataTransformerInterface;

class CustomerToStringTransformerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerToStringTransformer */
    private $transformer;

    /** @var DataTransformerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $dataTransformer;

    /** @var AccountCustomerManager|\PHPUnit\Framework\MockObject\MockObject */
    private $accountCustomerManager;

    protected function setUp(): void
    {
        $this->dataTransformer = $this->createMock(DataTransformerInterface::class);
        $this->accountCustomerManager = $this->getMockBuilder(AccountCustomerManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountCustomerByTarget'])
            ->getMock();

        $this->transformer = new CustomerToStringTransformer(
            $this->dataTransformer,
            $this->accountCustomerManager
        );
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform(string $value, Customer $expectedValue)
    {
        $decoded = json_decode($value, true);
        if (empty($decoded['value'])) {
            $this->dataTransformer->expects($this->once())
                ->method('reverseTransform')
                ->willReturnCallback(function ($value) {
                    $decoded = json_decode($value, true);
                    $entity = new $decoded['entityClass']();
                    ReflectionUtil::setId($entity, 1);

                    return $entity;
                });
        }
        $expectedAccount = $expectedValue->getAccount();
        $this->accountCustomerManager->expects($this->any())
            ->method('getAccountCustomerByTarget')
            ->willReturnCallback(function () use ($expectedAccount) {
                return (new Customer())->setTarget($expectedAccount);
            });
        $this->assertEquals(
            $expectedValue,
            $this->transformer->reverseTransform($value)
        );
    }

    public function reverseTransformProvider(): array
    {
        $existingAccount = new Account();
        ReflectionUtil::setId($existingAccount, 1);

        $newAccount = new Account();
        $newAccount->setName('new account');

        return [
            'new account'      => [
                json_encode(['value' => 'new account']),
                (new Customer())->setTarget($newAccount)
            ],
            'existing account' => [
                json_encode(['entityClass' => Account::class, 'entityId' => 1]),
                (new Customer())->setTarget($existingAccount),
            ],
        ];
    }

    public function testTransform()
    {
        $account = new Account();
        $account->setName('account');
        $customer = (new Customer())->setTarget($account);
        $this->dataTransformer->expects($this->any())
            ->method('transform')
            ->with($account)
            ->willReturn('transformedValue');

        $this->assertEquals(
            'transformedValue',
            $this->transformer->transform($customer)
        );
    }
}
