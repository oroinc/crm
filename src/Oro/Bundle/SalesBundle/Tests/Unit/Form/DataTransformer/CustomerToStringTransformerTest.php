<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Form\DataTransformer\CustomerToStringTransformer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub as CustomerStub;

class CustomerToStringTransformerTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerToStringTransformer */
    protected $transformer;

    /** @var DataTransformerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataTransformer;

    /** @var AccountCustomerManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $accountCustomerManager;

    public function setUp()
    {
        $this->dataTransformer = $this
            ->getMockBuilder('Symfony\Component\Form\DataTransformerInterface')
            ->getMock();

        $this->accountCustomerManager = $this
            ->getMockBuilder(AccountCustomerManager::class)
            ->disableOriginalConstructor()
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
                ->will($this->returnCallback(function ($value) {
                    $decoded = json_decode($value, true);
                    $entity       = new $decoded['entityClass'];
                    $accountIdRef = new \ReflectionProperty($decoded['entityClass'], 'id');
                    $accountIdRef->setAccessible(true);
                    $accountIdRef->setValue($entity, 1);

                    return $entity;
                }));
        }
        $expectedAccount = $expectedValue->getAccount();
        $this->accountCustomerManager->expects($this->any())
            ->method('getOrCreateAccountCustomerByTarget')
            ->will($this->returnCallback(function () use ($expectedAccount) {
                return (new Customer())->setAccount($expectedAccount);
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
        $accountIdRef = new \ReflectionProperty(Account::class, 'id');
        $accountIdRef->setAccessible(true);
        $existingAccount = new Account();
        $accountIdRef->setValue($existingAccount, 1);

        $newAccount = new Account();
        $newAccount->setName('new account');

        return [
            'new account'      => [
                json_encode(['value' => 'new account']),
                (new Customer())->setAccount($newAccount)
            ],
            'existing account' => [
                json_encode(['entityClass' => Account::class, 'entityId' => 1]),
                (new Customer())->setAccount($existingAccount),
            ],
        ];
    }

    /**
     * @dataProvider transformProvider
     *
     * @param Customer $value
     * @param string   $expectedValue
     */
    public function testTransform(Customer $value, $expectedValue)
    {
        $account = $value->getAccount();
        if ($account->getId()) {
            $this->dataTransformer->expects($this->any())
                ->method('transform')
                ->with($account)
                ->will($this->returnValue('parentTransform'));
        }

        $this->assertEquals(
            $expectedValue,
            $this->transformer->transform($value)
        );
    }

    /**
     * @return array
     */
    public function transformProvider()
    {
        $accountIdRef = new \ReflectionProperty(Account::class, 'id');
        $accountIdRef->setAccessible(true);
        $existingAccount = new Account();
        $existingAccount->setName('existing account');
        $accountIdRef->setValue($existingAccount, 1);

        $newAccount = new Account();
        $newAccount->setName('new account');

        return [
            'new account'      => [
                (new Customer())->setAccount($newAccount),
                json_encode(['value' => 'new account']),
            ],
            'existing account' => [
                (new CustomerStub())->setAccount($existingAccount),
                'parentTransform',
            ],
        ];
    }
}
