<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;

/**
 * @dbIsolation
 */
class CustomerAccountChangeSubscriberTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient();
    }

    /**
     * @return array
     */
    public function testAssignMagentoCustomerAccount()
    {
        $account = new Account();
        $account->setName('test');
        $magentoCustomer = new MagentoCustomer();
        $magentoCustomer
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setAccount($account);

        $salesCustomer = new Customer();
        $salesCustomer
            ->setCustomerTarget($magentoCustomer)
            ->setAccount($account);

        $this->flushAndRefresh($magentoCustomer, $salesCustomer);

        $this->assertSame($magentoCustomer->getAccount(), $salesCustomer->getAccount());

        $account = new Account();
        $account->setName('MagentoAccount');
        $magentoCustomer->setAccount($account);

        $this->flushAndRefresh($magentoCustomer, $salesCustomer);

        $this->assertSame($magentoCustomer->getAccount(), $salesCustomer->getAccount());
        $this->assertInstanceOf(Account::class, $magentoCustomer->getAccount());
        $this->assertInstanceOf(Account::class, $salesCustomer->getAccount());
        $this->assertEquals('MagentoAccount', $magentoCustomer->getAccount()->getName());
        $this->assertSame($magentoCustomer, $salesCustomer->getCustomerTarget());

        return [$magentoCustomer, $salesCustomer];
    }

    /**
     * @depends testAssignMagentoCustomerAccount
     *
     * @param array $customers
     *
     * @return array
     */
    public function testChangeMagentoCustomerAccount(array $customers)
    {
        /**
         * @var MagentoCustomer $magentoCustomer
         * @var Customer        $salesCustomer
         */
        list($magentoCustomer, $salesCustomer) = $customers;

        $account = new Account();
        $account->setName('ChangedAccount');
        $magentoCustomer->setAccount($account);
        $this->flushAndRefresh($magentoCustomer, $salesCustomer);

        $this->assertInstanceOf(Account::class, $magentoCustomer->getAccount());
        $this->assertInstanceOf(Account::class, $salesCustomer->getAccount());
        $this->assertSame($magentoCustomer->getAccount()->getName(), $salesCustomer->getAccount()->getName());
        $this->assertEquals('ChangedAccount', $magentoCustomer->getAccount()->getName());
        $this->assertSame($magentoCustomer, $salesCustomer->getCustomerTarget());

        return [$magentoCustomer, $salesCustomer];
    }

    /**
     * @depends testChangeMagentoCustomerAccount
     *
     * @param array $customers
     */
    public function testUnassignMagentoCustomerAccount(array $customers)
    {
        /**
         * @var MagentoCustomer $magentoCustomer
         * @var Customer        $salesCustomer
         */
        list($magentoCustomer, $salesCustomer) = $customers;

        $magentoCustomer->setAccount(null);
        $this->flushAndRefresh($magentoCustomer, $salesCustomer);

        $this->assertNull($magentoCustomer->getAccount());
        $this->assertSame($magentoCustomer, $salesCustomer->getCustomerTarget());
        $this->assertNotNull($salesCustomer->getAccount());
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
