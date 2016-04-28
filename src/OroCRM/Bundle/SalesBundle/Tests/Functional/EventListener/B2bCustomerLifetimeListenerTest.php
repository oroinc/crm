<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\EventListner;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\OpportunityStatus;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class B2bCustomerLifetimeListenerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures(['OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture\LoadSalesBundleFixtures']);
    }

    /**
     * @return Opportunity
     * @throws \Doctrine\ORM\ORMException
     */
    public function testCreateAffectsLifetimeIfValuable()
    {
        $em = $this->getEntityManager();
        /** @var B2bCustomer $b2bCustomer */
        $b2bCustomer = $this->getReference('default_b2bcustomer');
        $opportunity = new Opportunity();
        $opportunity->setName(uniqid('name'));
        $opportunity->setCustomer($b2bCustomer);
        $opportunity->setDataChannel($this->getReference('default_channel'));
        $opportunity->setCloseRevenue(50);
        $opportunity2 = clone $opportunity;

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        $em->persist($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunity2->setStatus($em->getReference($enumClass, 'won'));
        $em->persist($opportunity2);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(50, $b2bCustomer->getLifetime());

        return $opportunity2;
    }

    /**
     * @depends testCreateAffectsLifetimeIfValuable
     *
     * @param Opportunity $opportunity
     *
     * @return Opportunity
     */
    public function testChangeStatusAffectsLifetime(Opportunity $opportunity)
    {
        $em          = $this->getEntityManager();
        $b2bCustomer = $opportunity->getCustomer();
        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunity->setStatus($em->getReference($enumClass, 'lost'));

        $em->persist($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        $opportunity->setStatus($em->getReference($enumClass, 'won'));
        $opportunity->setCloseRevenue(100);

        $em->persist($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(100, $b2bCustomer->getLifetime());

        return $opportunity;
    }

    /**
     * @depends testChangeStatusAffectsLifetime
     *
     * @param Opportunity $opportunity
     *
     * @return Opportunity
     */
    public function testCustomerChangeShouldUpdateBothCustomersIfValuable(Opportunity $opportunity)
    {
        $em          = $this->getEntityManager();
        $b2bCustomer = $opportunity->getCustomer();

        $this->assertEquals(100, $b2bCustomer->getLifetime());

        $newCustomer = new B2bCustomer();
        $newCustomer->setName(uniqid('name'));
        $newCustomer->setDataChannel($opportunity->getDataChannel());

        $em->persist($newCustomer);
        $em->flush();

        $this->assertEquals(0, $newCustomer->getLifetime());

        $opportunity->setCustomer($newCustomer);
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
     *
     * @param Opportunity $opportunity
     *
     * @return Opportunity
     */
    public function testRemoveSubtractLifetime(Opportunity $opportunity)
    {
        $em          = $this->getEntityManager();
        $b2bCustomer = $opportunity->getCustomer();

        $em->remove($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        return $opportunity;
    }

    public function testRemoveOpportunityFromB2bCustomer()
    {
        $em = $this->getEntityManager();
        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        // add an opportunity to the database
        $opportunity = new Opportunity();
        $opportunity->setName('unset_b2bcustomer_test');
        $opportunity->setDataChannel($this->getReference('default_channel'));
        $opportunity->setCloseRevenue(50);
        $opportunity->setStatus($em->getReference($enumClass, 'won'));
        /** @var B2bCustomer $b2bCustomer */
        $b2bCustomer = $this->getReference('default_b2bcustomer');
        $b2bCustomer->addOpportunity($opportunity);
        $em->persist($opportunity);
        $em->flush();

        // check preconditions
        $this->assertEquals(50, $b2bCustomer->getLifetime());

        // test that lifetime value is recalculated if "won" opportunity is removed from the customer
        $b2bCustomer->removeOpportunity($opportunity);
        $em->flush();
        $this->assertEquals(0, $b2bCustomer->getLifetime());
    }

    /**
     * @depends testRemoveOpportunityFromB2bCustomer
     *
     * Test that no processing occured for b2bcustomers that were deleted
     * assert that onFlush event listeners not throwing exceptions
     */
    public function testRemovedB2bCustomer()
    {
        $em           = $this->getEntityManager();
        $organization = $em->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);

        $opportunity = new Opportunity();
        $opportunity->setName('remove_b2bcustomer_test');
        $opportunity->setDataChannel($this->getReference('default_channel'));
        $opportunity->setCloseRevenue(50);
        $opportunity->setBudgetAmount(50.00);
        $opportunity->setProbability(10);
        $opportunity->setStatus($em->getReference($enumClass, 'won'));
        $opportunity->setOrganization($organization);
        $opportunity->setCustomer($this->getReference('default_b2bcustomer'));

        /** @var B2bCustomer $b2bCustomer */
        $b2bCustomer = $this->getReference('default_b2bcustomer');
        $b2bCustomer->addOpportunity($opportunity);

        $em->persist($opportunity);
        $em->flush();

        $em->remove($b2bCustomer);
        $em->flush();
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
