<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Doctrine;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAccountOpportunitiesData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadLeadsData',
        ];
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
