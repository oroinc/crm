<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Tests\Performance\DataFixtures\Accounts;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCrmAccountsData extends AbstractFixture implements ContainerAwareInterface
{
    const FLUSH_MAX = 20;
    const MAX_RECORDS = 10000;

    private $maxRecords;

    /** @var ContainerInterface */
    private $container;

    /** @var EntityManagerInterface */
    private $em;

    /** @var Group[] */
    private $contactGroups;

    /** @var Source[] */
    private $contactSources;

    /** @var User[] */
    private $users;

    /** @var Country[] */
    private $countries;

    /** @var Organization */
    private $organization;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        if (isset($container->counter)) {
            $this->maxRecords = $container->counter;
        } else {
            $this->maxRecords = self::MAX_RECORDS;
        }

        $this->em = $container->get('doctrine')->getManager();

        $this->initSupportingEntities();
    }

    /**
     * Initialize all supporting entities
     */
    private function initSupportingEntities()
    {
        $this->contactGroups = $this->em->getRepository('OroContactBundle:Group')->findAll();
        $this->contactSources = $this->em->getRepository('OroContactBundle:Source')->findAll();
        $this->users = $this->em->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $this->em->getRepository('OroAddressBundle:Country')->findAll();
        $this->organization = $this->em->getRepository('OroOrganizationBundle:Organization')->getFirst();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadAccounts();
    }

    /**
     * Load Accounts
     *
     * @return void
     */
    public function loadAccounts()
    {
        $loadedRecords = 0;
        $averageTime = 0.0;
        $iteration = 0;
        while ($loadedRecords < $this->maxRecords) {
            $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . "data.csv", "r");
            echo "\nLoading...\n";
            if ($handle) {
                $headers = array();
                if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    //read headers
                    $headers = $data;
                }
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $s = microtime(true);
                    $data = array_combine($headers, array_values($data));
                    $account = $this->createAccount($data, $iteration);
                    $contact = $this->createContact($data, $iteration);
                    $contact->addAccount($account);
                    $account->setDefaultContact($contact);

                    $group = $this->contactGroups[rand(0, count($this->contactGroups)-1)];
                    $contact->addGroup($group);

                    $user = $this->users[rand(0, count($this->users)-1)];
                    $contact->setAssignedTo($user);
                    $contact->setReportsTo($contact);
                    $contact->setOwner($user);
                    $contact->setOrganization($this->organization);

                    $source = $this->contactSources[rand(0, count($this->contactSources)-1)];
                    $contact->setSource($source);

                    $account->setOwner($user);
                    $account->setOrganization($this->organization);

                    $this->em->persist($account);
                    $this->em->persist($contact);

                    $loadedRecords++;
                    if ($loadedRecords % self::FLUSH_MAX == 0) {
                        $this->em->flush();
                        $this->em->clear();

                        $this->initSupportingEntities();

                        $e = microtime(true);
                        echo ">> {$loadedRecords} " . ($e-$s) . "\n";
                        $averageTime += ($e-$s);
                    }

                    if ($loadedRecords == $this->maxRecords) {
                        break;
                    }
                }
                fclose($handle);
            }
            $iteration++;
            $this->em->flush();
        }
        $avg = $averageTime * self::FLUSH_MAX / $loadedRecords;
        echo ">> Average time: " . $avg . "\n";
        $this->container->averageTime = $avg;
    }

    /**
     * Create an Account
     *
     * @param array $data
     * @param int $iteration
     * @return Account
     */
    private function createAccount(array $data, $iteration = 0)
    {
        $account = new Account();

        $name = $data['Username'] . $data['MiddleInitial'] . '_' . $data['Surname'];
        if ($iteration) {
            $name .= '_' . $iteration;
        }
        $account->setName($name);

        return $account;
    }

    /**
     * Create a Contact
     *
     * @param array $data
     * @param int $iteration
     * @return Contact
     */
    private function createContact(array $data, $iteration = 0)
    {
        $contact = new Contact();

        $contact->setFirstName($data['GivenName']);
        $lastName = $data['Surname'];
        if ($iteration) {
            $lastName .= '_' . $iteration;
        }
        $contact->setLastName($lastName);
        $contact->setNamePrefix($data['Title']);

        $phone = new ContactPhone($data['TelephoneNumber']);
        $phone->setPrimary(true);
        $contact->addPhone($phone);

        $email = new ContactEmail($data['EmailAddress']);
        $email->setPrimary(true);
        $contact->addEmail($email);

        $date = \DateTime::createFromFormat('m/d/Y', $data['Birthday']);
        $contact->setBirthday($date);

        /** @var ContactAddress $address */
        $address = new ContactAddress();
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);
        $address->setPrimary(true);

        $isoCode = $data['Country'];
        $country = array_filter(
            $this->countries,
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
