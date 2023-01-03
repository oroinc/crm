<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerAccountChangeListenerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testSyncOnCreateCustomer(): array
    {
        $account = new Account();
        $account->setName('Account1');
        $b2bCustomer = new B2bCustomer();
        $b2bCustomer->setName('B2B Customer');
        $b2bCustomer->setCreatedAt(new \DateTime());
        $b2bCustomer->setUpdatedAt(new \DateTime());

        $customer = new Customer();
        $customer->setTarget($account, $b2bCustomer);

        $this->flushAndRefresh($b2bCustomer, $customer);

        $this->assertSame($b2bCustomer->getAccount(), $customer->getAccount());
        $this->assertSame($b2bCustomer, $customer->getTarget());
        return [$b2bCustomer, $customer];
    }

    /**
     * @depends testSyncOnCreateCustomer
     */
    public function testChangeCustomerAccount(array $customers)
    {
        /**
         * @var B2bCustomer $b2bCustomer
         * @var Customer $customer
         */
        [$b2bCustomer, $customer] = $customers;

        $account = new Account();
        $account->setName('Account2');
        $customer->setTarget($account, $b2bCustomer);

        $this->assertSame($b2bCustomer, $customer->getTarget());
        $this->assertNotSame($b2bCustomer->getAccount(), $customer->getAccount());

        $this->flushAndRefresh($customer);

        $this->assertSame($b2bCustomer->getAccount(), $customer->getAccount());
        $this->assertEquals('Account2', $b2bCustomer->getAccount()->getName());
        $this->assertSame($b2bCustomer, $customer->getTarget());
    }

    private function flushAndRefresh(object $entity): void
    {
        $em = $this->getEntityManager();
        $entities = func_get_args();

        array_walk($entities, [$em, 'persist']);
        $em->flush();
        array_walk($entities, [$em, 'refresh']);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManagerForClass(Customer::class);
    }
}
