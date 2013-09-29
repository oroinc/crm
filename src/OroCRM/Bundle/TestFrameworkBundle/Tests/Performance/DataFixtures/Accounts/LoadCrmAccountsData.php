<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\DataFixtures;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\Source;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\User;

class LoadCrmAccountsData extends AbstractFixture implements ContainerAwareInterface
{
    const FLUSH_MAX = 20;
    const MAX_RECORDS = 10000;

    protected $maxRecords;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Account Manager
     */
    protected $accountManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $accountRepository;

    /**
     * @var EntityManager
     */
    protected $contactManager;

    /**
     * @var EntityRepository
     */
    protected $contactRepository;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var EntityRepository
     */
    protected $userRepository;

    /**
     * @var EntityRepository
     */
    protected $countryRepository;

    /**
     * @var Group[]
     */
    protected $contactGroups;

    /**
     * @var Source[]
     */
    protected $contactSources;

    /**
     * @var User[]
     */
    protected $users;

    /**
     * @var Country[]
     */
    protected $countries;

    /** @var AbstractAttribute */
    protected $attributes;
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

        $this->accountManager = $container->get('orocrm_account.account.manager.flexible');
        $this->accountRepository = $this->accountManager->getFlexibleRepository();

        $this->contactManager = $container->get('doctrine.orm.entity_manager');
        $this->contactRepository = $this->contactManager->getRepository('OroCRMContactBundle:Contact');

        $this->userManager = $container->get('oro_user.manager');
        $this->userRepository = $this->userManager->getFlexibleRepository();

        $this->initSupportingEntities();
    }

    /**
     * Initialize all supporting entities
     */
    protected function initSupportingEntities()
    {
        $this->contactGroups = $this->contactManager->getRepository('OroCRMContactBundle:Group')->findAll();
        $this->contactSources = $this->contactManager->getRepository('OroCRMContactBundle:Source')->findAll();

        $userStorageManager = $this->userManager->getStorageManager();
        $this->users = $userStorageManager->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $userStorageManager->getRepository('OroAddressBundle:Country')->findAll();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadAttributes();
        $this->loadAccounts();
    }

    /**
     * Load attributes
     *
     * @return void
     */
    public function loadAttributes()
    {
    }

    /**
     * Load Accounts
     *
     * @return void
     */
    public function loadAccounts()
    {
        $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . "data.csv", "r");
        $averageTime = 0.0;
        if ($handle) {
            $i = 0;
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            echo "\nLoading...\n";
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $s = microtime(true);
                $data = array_combine($headers, array_values($data));
                $account = $this->createAccount($data);
                $contact = $this->createContact($data);
                $contact->addAccount($account);
                $account->setDefaultContact($contact);

                $group = $this->contactGroups[rand(0, count($this->contactGroups)-1)];
                $contact->addGroup($group);

                $user = $this->users[rand(0, count($this->users)-1)];
                $contact->setAssignedTo($user);
                $contact->setReportsTo($contact);
                $contact->setOwner($user);

                $source = $this->contactSources[rand(0, count($this->contactSources)-1)];
                $contact->setSource($source);

                $account->setOwner($user);

                $this->persist($this->accountManager, $account);
                $this->contactManager->persist($contact);

                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $this->flush($this->accountManager);
                    $this->contactManager->flush();
                    $this->contactManager->clear();

                    $this->initSupportingEntities();

                    $e = microtime(true);
                    echo ">> {$i} " . ($e-$s) . "\n";
                    $averageTime += ($e-$s);
                }

                if ($i % $this->maxRecords == 0) {
                    break;
                }
            }
            fclose($handle);
        }
        $this->flush($this->accountManager);
        $this->contactManager->flush();
        $avg = $averageTime * self::FLUSH_MAX / $this->maxRecords;
        echo ">> Average time: " . $avg . "\n";
        $this->container->averageTime = $avg;
    }

    /**
     * Create an Account
     *
     * @param array $data
     * @return Account
     */
    private function createAccount(array $data)
    {
        /** @var $account Account */
        $account = $this->accountManager->createFlexible();

        $account->setName($data['Username'] . $data['MiddleInitial'] . '_' . $data['Surname']);

        $this->setFlexibleAttributeValue($this->accountRepository, $account, 'phone', $data['TelephoneNumber']);
        $this->setFlexibleAttributeValue($this->accountRepository, $account, 'email', $data['EmailAddress']);
        $this->setFlexibleAttributeValue($this->accountRepository, $account, 'website', $data['Domain']);

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
        //$idRegion = 'AL';
        /** @var Collection $regions */
        $regions = $country->getRegions();

        $region = $regions->filter(
            function (Region $a) use ($idRegion) {
                return $a->getCode() == $idRegion;
            }
        );

        $address = new Address();
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);

        $address->setCountry($country);
        if (!$region->isEmpty()) {
            $address->setState($region->first());
        }

        $account->setShippingAddress($address);
        $account->setBillingAddress(clone $address);

        return $account;
    }

    /**
     * Create a Contact
     *
     * @param array $data
     * @return Contact
     */
    private function createContact(array $data)
    {
        $contact = new Contact();

        $contact->setFirstName($data['GivenName']);
        $contact->setLastName($data['Surname']);
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
            $address->setState($region->first());
        }

        $contact->addAddress($address);
        return $contact;
    }

    /**
     * Sets a flexible attribute value
     *
     * @param FlexibleEntityRepository $repository
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @throws \LogicException
     */
    private function setFlexibleAttributeValue(
        FlexibleEntityRepository $repository,
        AbstractFlexible $flexibleEntity,
        $attributeCode,
        $value
    ) {
        if ($attribute = $this->findAttribute($repository, $attributeCode)) {
            $this->getFlexibleValueForAttribute($flexibleEntity, $attribute)->setData($value);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Gets or creates a flexible value for attribute
     *
     * @param AbstractFlexible $flexibleEntity
     * @param AbstractAttribute $attribute
     * @return FlexibleValueInterface
     */
    private function getFlexibleValueForAttribute(AbstractFlexible $flexibleEntity, AbstractAttribute $attribute)
    {
        $flexibleValue = $flexibleEntity->getValue($attribute->getCode());

        return $flexibleValue;
    }

    /**
     * Finds an attribute
     *
     * @param FlexibleEntityRepository $repository
     * @param string $attributeCode
     * @return AbstractAttribute
     */
    private function findAttribute(FlexibleEntityRepository $repository, $attributeCode)
    {
        if (!isset($this->attributes[$repository->getClassName()][$attributeCode])) {
            $this->attributes[$repository->getClassName()][$attributeCode] =
                $repository->findAttributeByCode($attributeCode);
        }
        return $this->attributes[$repository->getClassName()][$attributeCode];
    }

    /**
     * Persist object
     *
     * @param mixed $manager
     * @param mixed $object
     */
    private function persist($manager, $object)
    {
        $manager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     *
     * @param mixed $manager
     */
    private function flush($manager)
    {
        $manager->getStorageManager()->flush();
    }
}
