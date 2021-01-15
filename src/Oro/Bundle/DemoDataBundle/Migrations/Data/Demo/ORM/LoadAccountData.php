<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadAccountData extends AbstractDemoFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Organization $organization */
        $organization = $this->getReference('default_organization');

        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        $handle  = fopen($dictionaryDir . DIRECTORY_SEPARATOR . "accounts.csv", "r");
        $headers = fgetcsv($handle, 1000, ",");

        $companies = [];

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $data = array_combine($headers, array_values($data));

            if (!isset($companies[$data['Company']])) {
                $account = new Account();
                $account->setName($data['Company']);
                $account->setOwner($this->getRandomUserReference());
                $account->setOrganization($organization);

                $this->em->persist($account);

                $companies[$data['Company']] = $data['Company'];
            }
        }
        fclose($handle);

        $this->em->flush();
    }
}
