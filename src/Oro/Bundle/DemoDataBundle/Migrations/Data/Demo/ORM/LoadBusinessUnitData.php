<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\Repository\BusinessUnitRepository;

/**
 * Loads business units.
 */
class LoadBusinessUnitData extends AbstractFixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $organization = $manager->getRepository(Organization::class)->getFirst();
        $this->addReference('default_organization', $organization);

        /** @var BusinessUnitRepository $businessUnitRepository */
        $businessUnitRepository = $manager->getRepository(BusinessUnit::class);
        /** @var BusinessUnit $mainBusinessUnit */
        $mainBusinessUnit = $businessUnitRepository->findOneBy(['name' => 'Main']);
        if (!$mainBusinessUnit) {
            $mainBusinessUnit = $businessUnitRepository->findOneBy(['name' => 'Acme, General']);
        }
        if (!$mainBusinessUnit) {
            throw new \RuntimeException('"Main" business unit is not defined');
        }

        $mainBusinessUnit->setName('Acme, General');
        $mainBusinessUnit->setEmail('general@acme.inc');
        $mainBusinessUnit->setPhone('798-682-5917');
        $manager->persist($mainBusinessUnit);
        $manager->flush();

        $this->addReference('default_main_business', $mainBusinessUnit);

        $crmBusinessUnit = new BusinessUnit();
        $crmBusinessUnit
            ->setName('Acme, West')
            ->setWebsite('http://www.example.com')
            ->setOrganization($organization)
            ->setEmail('west@acme.inc')
            ->setPhone('798-682-5918')
            ->setOwner($mainBusinessUnit);
        $manager->persist($crmBusinessUnit);
        $this->addReference('default_crm_business', $crmBusinessUnit);

        $coreBusinessUnit = new BusinessUnit();
        $coreBusinessUnit
            ->setName('Acme, East')
            ->setWebsite('http://www.magecore.com/')
            ->setOrganization($organization)
            ->setEmail('east@acme.inc')
            ->setPhone('798-682-5919')
            ->setOwner($mainBusinessUnit);
        $manager->persist($coreBusinessUnit);
        $this->addReference('default_core_business', $coreBusinessUnit);

        $manager->flush();
    }
}
