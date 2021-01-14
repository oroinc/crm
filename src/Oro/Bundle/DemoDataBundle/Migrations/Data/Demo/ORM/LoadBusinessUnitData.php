<?php
namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\Repository\BusinessUnitRepository;
use Oro\Bundle\OrganizationBundle\Entity\Repository\OrganizationRepository;

/**
 * Loads business units.
 */
class LoadBusinessUnitData extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var OrganizationRepository $organizationRepository */
        $organizationRepository = $manager->getRepository('OroOrganizationBundle:Organization');
        /** @var BusinessUnitRepository $businessUnitRepository */
        $businessUnitRepository = $manager->getRepository('OroOrganizationBundle:BusinessUnit');

        /** @var Organization $organization */
        $organization = $organizationRepository->getFirst();

        $this->addReference('default_organization', $organization);

        /** @var BusinessUnit $oroMain */
        $oroMain = $businessUnitRepository->findOneBy(array('name' => 'Main'));
        if (!$oroMain) {
            $oroMain = $businessUnitRepository->findOneBy(array('name' => 'Acme, General'));
        }

        if (!$oroMain) {
            throw new \Exception('"Main" business unit is not defined');
        }

        $oroMain->setName('Acme, General');
        $oroMain->setEmail('general@acme.inc');
        $oroMain->setPhone('798-682-5917');

        $manager->persist($oroMain);
        $manager->flush();

        $this->addReference('default_main_business', $oroMain);

        /** @var BusinessUnit $oroUnit */
        $oroUnit = new BusinessUnit();

        /** @var BusinessUnit $mageCoreUnit */
        $mageCoreUnit = new BusinessUnit();

        $oroUnit
            ->setName('Acme, West')
            ->setWebsite('http://www.example.com')
            ->setOrganization($organization)
            ->setEmail('west@acme.inc')
            ->setPhone('798-682-5918')
            ->setOwner($oroMain);

        $manager->persist($oroUnit);
        $this->addReference('default_crm_business', $oroUnit);

        $mageCoreUnit
            ->setName('Acme, East')
            ->setWebsite('http://www.magecore.com/')
            ->setOrganization($organization)
            ->setEmail('east@acme.inc')
            ->setPhone('798-682-5919')
            ->setOwner($oroMain);

        $manager->persist($mageCoreUnit);
        $manager->flush();

        $this->addReference('default_core_business', $mageCoreUnit);
    }
}
