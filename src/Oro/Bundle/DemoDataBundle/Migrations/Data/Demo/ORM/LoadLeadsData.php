<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads new Lead entities.
 */
class LoadLeadsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    private const FLUSH_MAX = 50;

    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadUsersData::class,
            LoadAccountData::class,
            LoadLeadSourceData::class,
            LoadChannelData::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $this->loadLeads($manager, $tokenStorage);
        $this->loadSources($manager);
        $tokenStorage->setToken(null);
    }

    private function loadLeads(ObjectManager $manager, TokenStorageInterface $tokenStorage): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $countries = $manager->getRepository(Country::class)->findAll();
        $organization = $this->getReference('default_organization');

        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        $handle = fopen($dictionaryDir . DIRECTORY_SEPARATOR . 'leads.csv', 'r');
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ',')) !== false) {
                //read headers
                $headers = $data;
            }
            $randomUser = \count($users) - 1;
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $user = $users[mt_rand(0, $randomUser)];
                $tokenStorage->setToken(new UsernamePasswordOrganizationToken(
                    $user,
                    'main',
                    $organization,
                    $user->getUserRoles()
                ));

                $data = array_combine($headers, array_values($data));

                $lead = $this->createLead($manager, $data, $user, $organization, $countries);
                $manager->persist($lead);

                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $manager->flush();
                }
            }

            $manager->flush();
            fclose($handle);
        }
    }

    private function loadSources(ObjectManager $manager): void
    {
        $sources = $manager->getRepository(EnumOption::class)
            ->findBy(['enumCode' => 'lead_source']);
        $randomSource = \count($sources) - 1;
        $leads = $manager->getRepository(Lead::class)->findAll();
        foreach ($leads as $lead) {
            /** @var Lead $lead */
            $source = $sources[mt_rand(0, $randomSource)];
            $lead->setSource($source);
            $manager->persist($lead);
        }
        $manager->flush();
    }

    private function createLead(
        ObjectManager $manager,
        array $data,
        User $user,
        Organization $organization,
        array $countries
    ): Lead {
        $lead = new Lead();
        $defaultStatus = $manager->getRepository(EnumOption::class)->find(
            ExtendHelper::buildEnumOptionId(
                Lead::INTERNAL_STATUS_CODE,
                ExtendHelper::buildEnumInternalId('new')
            )
        );

        $lead->setStatus($defaultStatus);
        $lead->setName($data['Company']);
        $lead->setFirstName($data['GivenName']);
        $lead->setLastName($data['Surname']);

        $phone = new LeadPhone($data['TelephoneNumber']);
        $phone->setPrimary(true);
        $lead->addPhone($phone);

        $email = new LeadEmail($data['EmailAddress']);
        $email->setPrimary(true);
        $lead->addEmail($email);

        $lead->setCompanyName($data['Company']);
        $lead->setOwner($user);
        $lead->setOrganization($organization);
        $lead->setTwitter($data['Twitter']);

        $address = new LeadAddress();
        $address->setLabel('Primary Address');
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);
        $address->setPrimary(true);

        $isoCode = $data['Country'];
        $country = array_filter(
            $countries,
            function (Country $a) use ($isoCode) {
                return $a->getIso2Code() == $isoCode;
            }
        );

        $country = array_values($country);
        /** @var Country $country */
        $country = $country[0];

        $idRegion = $data['State'];
        /** @var Collection $regions */
        $regions = $country->getRegions();

        $region = $regions->filter(
            function (Region $a) use ($idRegion) {
                return $a->getCode() == $idRegion;
            }
        );

        $address->setCountry($country);
        if (!$region->isEmpty()) {
            $address->setRegion($region->first());
        }

        $lead->addAddress($address);

        return $lead;
    }
}
