<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CustomerAccountChangeListenerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    /**
     * @return array
     */
    public function testSyncOnCreateCustomer()
    {
        $account = new Account();
        $account->setName('Account1');
        $magentoCustomer = new MagentoCustomer();
        $magentoCustomer
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $customer = new Customer();
        $customer->setTarget($account, $magentoCustomer);

        $this->flushAndRefresh($magentoCustomer, $customer);

        $this->assertSame($magentoCustomer->getAccount(), $customer->getAccount());
        $this->assertSame($magentoCustomer, $customer->getTarget());
        return [$magentoCustomer, $customer];
    }

    /**
     * @depends testSyncOnCreateCustomer
     *
     * @param array $customers
     *
     * @return array
     */
    public function testChangeCustomerAccount(array $customers)
    {
        /**
         * @var MagentoCustomer $magentoCustomer
         * @var Customer        $customer
         */
        list($magentoCustomer, $customer) = $customers;

        $account = new Account();
        $account->setName('Account2');
        $customer->setTarget($account, $magentoCustomer);

        $this->assertSame($magentoCustomer, $customer->getTarget());
        $this->assertNotSame($magentoCustomer->getAccount(), $customer->getAccount());

        $this->flushAndRefresh($customer);

        $this->assertSame($magentoCustomer->getAccount(), $customer->getAccount());
        $this->assertEquals('Account2', $magentoCustomer->getAccount()->getName());
        $this->assertSame($magentoCustomer, $customer->getTarget());
    }

    /**
     * @param object $entity
     * @param object $_
     */
    protected function flushAndRefresh($entity, $_ = null)
    {
        $em       = $this->getEntityManager();
        $entities = func_get_args();

        array_walk($entities, [$em, 'persist']);
        $em->flush();
        array_walk($entities, [$em, 'refresh']);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManagerForClass(Customer::class);
    }
}
