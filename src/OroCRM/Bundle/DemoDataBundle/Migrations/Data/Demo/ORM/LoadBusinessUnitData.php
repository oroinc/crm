<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadBusinessUnitData extends AbstractFixture implements ContainerAwareInterface
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

        $this->organizationManager = $container->get('doctrine')->getManager();

        $this->businessUnitRepository = $this->organizationManager->getRepository('OroOrganizationBundle:BusinessUnit');
        $this->organizationRepository = $this->organizationManager->getRepository('OroOrganizationBundle:Organization');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Organization $organization */
        $organization = $this->organizationRepository->getFirst();

        $this->addReference('default_organization', $organization);

        /** @var BusinessUnit $oroMain */
        $oroMain = $this->businessUnitRepository->findOneBy(array('name' => 'Main'));
        if (!$oroMain) {
            $oroMain = $this->businessUnitRepository->findOneBy(array('name' => 'Acme, General'));
        }

        if (!$oroMain) {
            throw new \Exception('"Main" business unit is not defined');
        }

        $oroMain->setName('Acme, General');
        $oroMain->setEmail('general@acme.inc');
        $oroMain->setPhone('798-682-5917');

        $this->persistAndFlush($this->organizationManager, $oroMain);

        $this->addReference('default_main_business', $oroMain);

        /** @var BusinessUnit $oroUnit */
        $oroUnit = new BusinessUnit();

        /** @var BusinessUnit $mageCoreUnit */
        $mageCoreUnit = new BusinessUnit();

        $oroUnit
            ->setName('Acme, West')
            ->setWebsite('http://www.orocrm.com')
            ->setOrganization($organization)
            ->setEmail('west@acme.inc')
            ->setPhone('798-682-5918')
            ->setOwner($oroMain);

        $this->persist($this->organizationManager, $oroUnit);
        $this->addReference('default_crm_business', $oroUnit);

        $mageCoreUnit
            ->setName('Acme, East')
            ->setWebsite('http://www.magecore.com/')
            ->setOrganization($organization)
            ->setEmail('east@acme.inc')
            ->setPhone('798-682-5919')
            ->setOwner($oroMain);

        $this->persistAndFlush($this->organizationManager, $mageCoreUnit);
        $this->addReference('default_core_business', $mageCoreUnit);
    }

    /**
     * @param EntityManager $manager
     * @param mixed         $object
     */
    private function persistAndFlush($manager, $object)
    {
        $manager->persist($object);
        $manager->flush();
    }

    /**
     * @param EntityManager $manager
     * @param mixed         $object
     */
    private function persist($manager, $object)
    {
        $manager->persist($object);
    }
}
