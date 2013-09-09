<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadBusinessUnitData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /** @var  EntityRepository */
    protected $businessUnitRepository;

    /** @var  EntityRepository */
    protected $organizationRepository;

    /** @var  EntityManager */
    protected $organizationManager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->organizationManager = $container->get('doctrine.orm.entity_manager');

        $this->businessUnitRepository = $this->organizationManager->getRepository('OroOrganizationBundle:BusinessUnit');
        $this->organizationRepository = $this->organizationManager->getRepository('OroOrganizationBundle:Organization');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        /** @var Organization $organization */
        $organization = $this->organizationRepository->findOneBy(array('name' => 'default'));
        if (!$organization) {
            $organization = $this->organizationRepository->findOneBy(array('name' => 'Oro, Inc'));
        }
        if (!$organization) {
            throw new \Exception('"default" company is not defined');
        }

        $organization->setName('Oro, Inc');

        $this->persistAndFlush($this->organizationManager, $organization);
        $this->addReference('default_organization', $organization);

        /** @var BusinessUnit $oroMain */
        $oroMain = $this->businessUnitRepository->findOneBy(array('name' => 'Main'));
        if (!$oroMain ) {
            $oroMain = $this->businessUnitRepository->findOneBy(array('name' => 'Oro, Inc'));
        }

        if (!$oroMain) {
            throw new \Exception('"Main" business unit is not defined');
        }

        $oroMain->setName('Oro, Inc');

        $this->persistAndFlush($this->organizationManager, $oroMain);

        $this->addReference('default_main_business', $oroMain);

        /** @var BusinessUnit $oroUnit */
        $oroUnit = new BusinessUnit();

        /** @var BusinessUnit $mageCoreUnit */
        $mageCoreUnit = new BusinessUnit();

        $oroUnit
            ->setName('OroCRM')
            ->setWebsite('http://www.orocrm.com')
            ->setOrganization($organization)
            ->setOwner($oroMain);

        $this->persistAndFlush($this->organizationManager, $oroUnit);
        $this->addReference('default_crm_business', $oroUnit);

        $mageCoreUnit
            ->setName('MageCore')
            ->setWebsite('http://www.magecore.com/')
            ->setOrganization($organization)
            ->setOwner($oroMain);

        $this->persistAndFlush($this->organizationManager, $mageCoreUnit);
        $this->addReference('default_core_business', $mageCoreUnit);
    }

    /**
     * @param EntityManager $manager
     * @param mixed $object
     */
    private function persistAndFlush($manager, $object)
    {
        $manager->persist($object);
        $manager->flush();
    }
    public function getOrder()
    {
        return 100;
    }
}
