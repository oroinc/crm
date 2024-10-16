<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\ContactBundle\Entity\Source;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loads contact demo data
 */
class LoadContactData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadAccountData::class,
            LoadContactGroupData::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $accounts = $manager->getRepository(Account::class)->findAll();
        $contactGroups = $manager->getRepository(Group::class)->findAll();
        $contactSources = $manager->getRepository(Source::class)->findAll();
        $organization = $this->getReference('default_organization');
        $countries = $manager->getRepository(Country::class)->findAll();

        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        $handle = fopen($dictionaryDir . DIRECTORY_SEPARATOR. "accounts.csv", "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $randomUser = \count($users) - 1;
            $randomContactGroup = \count($contactGroups) - 1;
            $randomContactSource = \count($contactSources) - 1;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                $contact = $this->createContact($data, $organization, $countries);

                $company = $data['Company'];
                $account = array_filter(
                    $accounts,
                    function (Account $a) use ($company) {
                        return $a->getName() == $company;
                    }
                );
                $account = reset($account);
                $contact->addAccount($account);

                $group = $contactGroups[rand(0, $randomContactGroup)];
                $contact->addGroup($group);

                $user = $users[rand(0, $randomUser)];
                $contact->setAssignedTo($user);
                $contact->setReportsTo($contact);
                $contact->setOwner($user);

                $source = $contactSources[rand(0, $randomContactSource)];
                $contact->setSource($source);
                $account->setDefaultContact($contact);

                $manager->persist($contact);
                $manager->persist($account);
            }

            $manager->flush();
            fclose($handle);
        }
    }

    private function createContact(array $data, Organization $organization, array $countries): Contact
    {
        $contact = new Contact();

        $contact->setFirstName($data['GivenName']);
        $contact->setLastName($data['Surname']);
        $contact->setNamePrefix($data['Title']);
        $contact->setGender($data['Gender']);
        $contact->setOrganization($organization);

        $phone = new ContactPhone($data['TelephoneNumber']);
        $phone->setPrimary(true);
        $contact->addPhone($phone);

        $email = new ContactEmail($data['EmailAddress']);
        $email->setPrimary(true);
        $contact->addEmail($email);

        $date = \DateTime::createFromFormat('m/d/Y', $data['Birthday']);
        $contact->setBirthday($date);

        $address = new ContactAddress();
        $address->setLabel('Primary Address');
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);
        $address->setPrimary(true);
        $address->setOwner($contact);

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

        $contact->addAddress($address);

        return $contact;
    }
}
