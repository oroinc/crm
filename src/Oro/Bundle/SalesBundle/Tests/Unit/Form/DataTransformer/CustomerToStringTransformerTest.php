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
    protected $transformer;

    /** @var DataTransformerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $dataTransformer;

    /** @var AccountCustomerManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $accountCustomerManager;

    protected function setUp(): void
    {
        $this->dataTransformer = $this
            ->getMockBuilder('Symfony\Component\Form\DataTransformerInterface')
            ->getMock();

        $this->accountCustomerManager = $this
            ->getMockBuilder(AccountCustomerManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['createCustomer'])
            ->getMock();

        $this->transformer = new CustomerToStringTransformer(
            $this->dataTransformer,
            $this->accountCustomerManager
        );
    }

    /**
     * @dataProvider reverseTransformProvider
     *
     * @param string   $value
     * @param Customer $expectedValue
     */
    public function testReverseTransform($value, Customer $expectedValue)
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
            ->will($this->returnCallback(function () use ($expectedAccount) {
                return (new Customer())->setTarget($expectedAccount);
            }));
        $this->assertEquals(
            $expectedValue,
            $this->transformer->reverseTransform($value)
        );
    }

    /**
     * @return array
     */
    public function reverseTransformProvider()
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
            ->will($this->returnValue('transformedValue'));

        $this->assertEquals(
            'transformedValue',
            $this->transformer->transform($customer)
        );
    }
}
