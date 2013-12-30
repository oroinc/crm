<?php

namespace OroCRM\Bundle\DemoDataBundle\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\Doctrine;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAccountOpportunitiesData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 400;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $accounts      = $om->getRepository('OroCRMAccountBundle:Account')->findAll();
        $opportunities = $om->getRepository('OroCRMSalesBundle:Opportunity')->findAll();
        $randomAccounts = count($accounts) - 1;
        foreach ($opportunities as $opportunity) {
            $opportunity->setAccount($accounts[rand(0, $randomAccounts)]);
            $om->persist($opportunity);
        }
        $om->flush();
    }
}
