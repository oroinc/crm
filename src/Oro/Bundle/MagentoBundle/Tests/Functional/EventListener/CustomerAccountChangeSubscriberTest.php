<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer as SalesCustomer;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class CustomerAccountChangeSubscriberTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient();
    }

    public function testAssignMagentoCustomerAccount()
    {
        $magentoCustomer = (new MagentoCustomer())
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());
        $salesCustomer = (new SalesCustomer())
            ->setTarget($magentoCustomer);
        $this->flushAndRefresh($magentoCustomer, $salesCustomer);

        $this->assertNull($magentoCustomer->getAccount());
        $this->assertNull($magentoCustomer->getAccount());

        $magentoCustomer->setAccount(
            (new Account())
                ->setname('MagentoAccount')
        );
        $this->flushAndRefresh($magentoCustomer, $salesCustomer);

        $this->assertInstanceOf(Account::class, $magentoCustomer->getAccount());
        $this->assertInstanceOf(Account::class, $salesCustomer->getAccount());
        $this->assertSame($magentoCustomer->getAccount(), $salesCustomer->getAccount());
        $this->assertEquals('MagentoAccount', $magentoCustomer->getAccount()->getName());
        $this->assertSame($magentoCustomer, $salesCustomer->getTarget());

        return [$magentoCustomer, $salesCustomer];
    }

    /**
     * @depends testAssignMagentoCustomerAccount
     */
    public function testChangeMagentoCustomerAccount(array $customers)
    {
        list($magentoCustomer, $salesCustomer) = $customers;

        $magentoCustomer->setAccount(
            (new Account())
                ->setName('ChangedAccount')
        );
        $this->flushAndRefresh($magentoCustomer, $salesCustomer);

        $this->assertInstanceOf(Account::class, $magentoCustomer->getAccount());
        $this->assertInstanceOf(Account::class, $salesCustomer->getAccount());
        $this->assertSame($magentoCustomer->getAccount()->getName(), $salesCustomer->getAccount()->getName());
        $this->assertEquals('ChangedAccount', $magentoCustomer->getAccount()->getName());
        $this->assertSame($magentoCustomer, $salesCustomer->getTarget());

        return [$magentoCustomer, $salesCustomer];
    }

    /**
     * @depends testChangeMagentoCustomerAccount
     */
    public function testUnassignMagentoCustomerAccount(array $customers)
    {
        list($magentoCustomer, $salesCustomer) = $customers;

        $magentoCustomer->setAccount(null);
        $this->flushAndRefresh($magentoCustomer, $salesCustomer);

        $this->assertNull($magentoCustomer->getAccount());
        $this->assertNull($magentoCustomer->getAccount());
        $this->assertSame($magentoCustomer, $salesCustomer->getTarget());
    }

    /**
     * @param object $entity
     * @param object $_
     */
    protected function flushAndRefresh($entity, $_ = null)
    {
        $em = $this->getEntityManager();
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
        return $this->getContainer()->get('doctrine')->getManagerForClass(SalesCustomer::class);
    }
}
