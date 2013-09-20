<?php

namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\Doctrine;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAccountOpportunitiesData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
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
        $accounts = $om->getRepository('OroCRMAccountBundle:Account')->findAll();
        $opportunities = $om->getRepository('OroCRMSalesBundle:Opportunity')->findAll();

        foreach ($opportunities as $opportunity) {
            $opportunity->setAccount(array_rand($accounts));
            $om->persist($opportunity);
        }
        $om->flush();
    }
}
