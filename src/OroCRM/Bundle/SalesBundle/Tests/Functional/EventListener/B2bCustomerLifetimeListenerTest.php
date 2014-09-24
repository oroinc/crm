<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\EventListner;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

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

        $opportunity2->setStatus($em->getReference('OroCRMSalesBundle:OpportunityStatus', 'won'));
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
        $opportunity->setStatus($em->getReference('OroCRMSalesBundle:OpportunityStatus', 'lost'));

        $em->persist($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());

        $opportunity->setStatus($em->getReference('OroCRMSalesBundle:OpportunityStatus', 'won'));
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
     */
    public function testRemoveSubtractLifetime(Opportunity $opportunity)
    {
        $em          = $this->getEntityManager();
        $b2bCustomer = $opportunity->getCustomer();

        $em->remove($opportunity);
        $em->flush();
        $em->refresh($b2bCustomer);

        $this->assertEquals(0, $b2bCustomer->getLifetime());
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
