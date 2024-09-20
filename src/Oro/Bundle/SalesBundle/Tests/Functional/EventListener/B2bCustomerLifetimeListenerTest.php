<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class B2bCustomerLifetimeListenerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadSalesBundleFixtures::class, LoadOrganization::class]);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    public function testCreateAffectsLifetimeIfValuable(): Opportunity
    {
        $em = $this->getEntityManager();
        /** @var B2bCustomer $b2bCustomer */
        $b2bCustomer = $this->getReference('default_b2bcustomer');
        $accountCustomer = $this->getReference('default_account_customer');
        $opportunity = new Opportunity();
        $opportunity->setName(uniqid('name'));
        $opportunity->setCustomerAssociation($accountCustomer);
        $closeRevenue = MultiCurrency::create(50, 'USD');
        $opportunity->setCloseRevenue($closeRevenue);
        $opportunity2 = clone $opportunity;

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        $em->persist($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        $opportunity2->setStatus($em->getReference(
            EnumOption::class,
            ExtendHelper::buildEnumOptionId(Opportunity::INTERNAL_STATUS_CODE, 'won')
        ));
        $em->persist($opportunity2);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(50, $b2bCustomer->getLifetime());

        return $opportunity2;
    }

    /**
     * @depends testCreateAffectsLifetimeIfValuable
     */
    public function testChangeStatusAffectsLifetime(Opportunity $opportunity): Opportunity
    {
        $em = $this->getEntityManager();
        $b2bCustomer = $opportunity->getCustomerAssociation()->getTarget();
        $opportunity->setStatus($em->getReference(
            EnumOption::class,
            ExtendHelper::buildEnumOptionId(Opportunity::INTERNAL_STATUS_CODE, 'lost')
        ));

        $em->persist($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        $opportunity->setStatus($em->getReference(
            EnumOption::class,
            ExtendHelper::buildEnumOptionId(Opportunity::INTERNAL_STATUS_CODE, 'won')
        ));
        $closeRevenue = MultiCurrency::create(100, 'USD');
        $opportunity->setCloseRevenue($closeRevenue);

        $em->persist($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(100, $b2bCustomer->getLifetime());

        return $opportunity;
    }

    /**
     * @depends testChangeStatusAffectsLifetime
     */
    public function testCustomerChangeShouldUpdateBothCustomersIfValuable(Opportunity $opportunity): Opportunity
    {
        $em = $this->getEntityManager();
        /** @var B2bCustomer $b2bCustomer */
        $b2bCustomer = $opportunity->getCustomerAssociation()->getTarget();
        $this->assertEquals(100, $b2bCustomer->getLifetime());
        $newCustomer = new B2bCustomer();
        $newCustomer->setName(uniqid('name'));
        $account = $this->getReference('default_account');
        $newCustomer->setAccount($account);
        $em->persist($newCustomer);
        $em->flush();

        $this->assertEquals(0, $newCustomer->getLifetime());

        $opportunity->setCustomerAssociation(
            self::getContainer()->get('oro_sales.manager.account_customer')->getAccountCustomerByTarget($newCustomer)
        );
        $em->persist($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);
        $em->refresh($newCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());
        $this->assertEquals(100, $newCustomer->getLifetime());

        return $opportunity;
    }

    /**
     * @depends testCustomerChangeShouldUpdateBothCustomersIfValuable
     */
    public function testRemoveSubtractLifetime(Opportunity $opportunity): Opportunity
    {
        $b2bCustomer = $opportunity->getCustomerAssociation()->getCustomerTarget();

        $em = $this->getEntityManager();
        $em->remove($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        return $opportunity;
    }

    public function testRemoveOpportunityFromB2bCustomer()
    {
        $em = $this->getEntityManager();
        // add an opportunity to the database
        $opportunity = new Opportunity();
        $opportunity->setName('unset_b2bcustomer_test');
        $closeRevenue = MultiCurrency::create(50, 'USD');
        $opportunity->setCloseRevenue($closeRevenue);
        $opportunity->setStatus($em->getReference(
            EnumOption::class,
            ExtendHelper::buildEnumOptionId(Opportunity::INTERNAL_STATUS_CODE, 'won')
        ));
        $opportunity->setCustomerAssociation($this->getReference('default_account_customer'));

        /** @var B2bCustomer $b2bCustomer */
        $b2bCustomer = $this->getReference('default_b2bcustomer');

        $em->persist($opportunity);
        $em->flush();

        // check preconditions
        $this->assertEquals(50, $b2bCustomer->getLifetime());

        // test that lifetime value is recalculated if "won" opportunity is removed from the customer
        $opportunity->setCustomerAssociation(null);

        $em->flush();
        $this->assertEquals(0, $b2bCustomer->getLifetime());
    }

    /**
     * @depends testRemoveOpportunityFromB2bCustomer
     *
     * Test that no processing occurred for b2b customers that were deleted
     * assert that onFlush event listeners not throwing exceptions
     */
    public function testRemovedB2bCustomer()
    {
        $em = $this->getEntityManager();

        /** @var Customer $customer */
        $customer = $this->getReference('default_account_customer');

        $opportunity = new Opportunity();
        $opportunity->setName('remove_b2bcustomer_test');
        $budgetAmount = MultiCurrency::create(50.00, 'USD');
        $closeRevenue = $budgetAmount;
        $opportunity->setCloseRevenue($closeRevenue);
        $opportunity->setBudgetAmount($budgetAmount);
        $opportunity->setProbability(0.1);
        $opportunity->setStatus($em->getReference(
            EnumOption::class,
            ExtendHelper::buildEnumOptionId(Opportunity::INTERNAL_STATUS_CODE, 'won')
        ));
        $opportunity->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $opportunity->setCustomerAssociation($customer);

        /** @var B2bCustomer $b2bCustomer */
        $b2bCustomer = $customer->getTarget();
        $customer->setCustomerTarget(null);
        $em->persist($opportunity);
        $em->flush();

        $em->remove($b2bCustomer);
        $em->flush();
    }
}
