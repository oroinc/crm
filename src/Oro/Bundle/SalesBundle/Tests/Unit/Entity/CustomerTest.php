<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\AccountAwareCustomerTarget;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub as Customer;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider setTargetProvider
     */
    public function testSetTarget(Customer $customer, $target, $expectedTarget, $expectedAccout)
    {
        $customer->setTarget($target);
        $this->assertEquals($expectedTarget, $customer->getTarget());
        $this->assertEquals($expectedAccout, $customer->getAccount());
    }

    public function setTargetProvider()
    {
        return [
            'set null as target' => [
                new Customer(),
                null,
                null,
                null,
            ],
            'set account as target' => [
                new Customer(),
                (new Account())->setName('account'),
                (new Account())->setName('account'),
                (new Account())->setName('account'),
            ],
            'set target without account' => [
                new Customer(),
                new AccountAwareCustomerTarget(1),
                new AccountAwareCustomerTarget(1),
                null,
            ],
            'set target with account' => [
                new Customer(),
                new AccountAwareCustomerTarget(1, (new Account)->setName('account')),
                new AccountAwareCustomerTarget(1, (new Account)->setName('account')),
                (new Account)->setName('account'),
            ],
            'remove target with account' => [
                (new Customer())->setTarget(new AccountAwareCustomerTarget(1, (new Account)->setName('account'))),
                null,
                null,
                null,
            ],
            'change to target with account' => [
                (new Customer())->setTarget(new AccountAwareCustomerTarget(1, (new Account)->setName('account'))),
                new AccountAwareCustomerTarget(2, (new Account)->setName('account2')),
                new AccountAwareCustomerTarget(2, (new Account)->setName('account2')),
                (new Account)->setName('account2'),
            ],
            'change to target without account' => [
                (new Customer())->setTarget(new AccountAwareCustomerTarget(1)),
                new AccountAwareCustomerTarget(2),
                new AccountAwareCustomerTarget(2),
                null,
            ],
        ];
    }
}
