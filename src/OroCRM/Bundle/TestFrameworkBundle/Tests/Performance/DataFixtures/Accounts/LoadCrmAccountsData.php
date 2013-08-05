<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\DataFixtures;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\PhoneType;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\EmailType;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
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

    /** @var array Lead Sources */
    protected $sources = array('other', 'call', 'TV', 'website');
    /**
     * @var Account Manager
     */
    protected $accountManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $accountRepository;

    /**
     * @var Contact Manager
     */
    protected $contactManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $contactRepository;

    /**
     * @var User Manager
     */
    protected $userManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $userRepository;

    /**
     * @var FlexibleEntityRepository
     */
    protected $countryRepository;

    protected $groups;
    protected $users;
    protected $countries;


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

        $this->accountManager = $this->container->get('orocrm_account.account.manager.flexible');
        $this->accountRepository = $this->accountManager->getFlexibleRepository();

        $this->contactManager = $this->container->get('orocrm_contact.manager.flexible');
        $this->contactRepository = $this->contactManager->getFlexibleRepository();
        $this->groups = $this->contactManager
            ->getStorageManager()
            ->getRepository('OroCRMContactBundle:Group')
            ->findAll();

        $this->userManager = $this->container->get('oro_user.manager');
        $this->userRepository = $this->userManager->getFlexibleRepository();
        $this->users = $this->userManager
            ->getStorageManager()
            ->getRepository('OroUserBundle:User')
            ->findAll();

        $this->countries = $this->userManager
            ->getStorageManager()
            ->getRepository('OroAddressBundle:Country')
            ->findAll();
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
        if ($handle) {
            $i = 0;
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            echo "\nLoading...\n";
            $averageTime = 0.0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $s = microtime(true);
                $data = array_combine($headers, array_values($data));
                $account = $this->createAccount($data);
                $contact = $this->createContact($data);
                $contact->addAccount($account);
                $group = $this->groups[rand(0, count($this->groups)-1)];
                $contact->addGroup($group);
                $user = $this->users[rand(0, count($this->users)-1)];
                $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'assigned_to', $user);
                $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'reports_to', $contact);
                $source = $this->sources[rand(0, count($this->sources)-1)];
                $this->setFlexibleAttributeValueOption($this->contactRepository, $contact, 'source', $source);


                $this->persist($this->accountManager, $account);
                $this->persist($this->contactManager, $contact);

                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $this->flush($this->accountManager);
                    $this->flush($this->contactManager);
                    $this->contactManager->getStorageManager()->clear();

                    $this->groups = $this->contactManager
                        ->getStorageManager()
                        ->getRepository('OroCRMContactBundle:Group')
                        ->findAll();
                    $this->users = $this->userManager
                        ->getStorageManager()
                        ->getRepository('OroUserBundle:User')
                        ->findAll();
                    $this->countries = $this->userManager
                        ->getStorageManager()
                        ->getRepository('OroAddressBundle:Country')
                        ->findAll();

                    $e = microtime(true);
                    echo ">> {$i} " . ($e-$s) . "\n";
                    $averageTime += ($e-$s);
                    ob_flush();
                }

                if ($i % $this->maxRecords == 0) {
                    break;
                }
            }
            fclose($handle);
            $this->flush($this->accountManager);
            $this->flush($this->contactManager);
            echo ">> Average time: " . ($averageTime / ($this->maxRecords / self::FLUSH_MAX)) . "\n";
            $this->container->averageTime = $averageTime / ($this->maxRecords / self::FLUSH_MAX);
        }
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

        $address = new Address();
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);

        $isoCode = $data['Country'];
        $country = array_filter(
            $this->countries,
            function ($a) use ($isoCode) {
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
            function ($a) use ($idRegion) {
                return $a->getCode() == $idRegion;
            }
        );

        $address->setCountry($country);
        if (!$region->isEmpty()) {
            $address->setState($region->first());
        }

        $this->setFlexibleAttributeValue($this->accountRepository, $account, 'shipping_address', $address);
        $a = clone $address;
        $this->setFlexibleAttributeValue($this->accountRepository, $account, 'billing_address', $a);

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
        /** @var $contact  Contact */
        $contact = $this->contactManager->createFlexible();

        $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'first_name', $data['GivenName']);
        $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'last_name', $data['Surname']);
        $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'title', $data['Title']);
        $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'phone', $data['TelephoneNumber']);
        $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'email', $data['EmailAddress']);

        $date = date('m/d/y', strtotime($data['Birthday']));
        $date = new \DateTime($date);
        $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'birthday', $date);

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
            function ($a) use ($isoCode) {
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
            function ($a) use ($idRegion) {
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
     * Sets a flexible attribute value as option with given value
     *
     * @param FlexibleEntityRepository $repository
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @throws \LogicException
     */
    private function setFlexibleAttributeValueOption(
        FlexibleEntityRepository $repository,
        AbstractFlexible $flexibleEntity,
        $attributeCode,
        $value
    ) {
        if ($attribute = $this->findAttribute($repository, $attributeCode)) {
            $option = $this->findAttributeOptionWithValue($attribute, $value);
            $this->getFlexibleValueForAttribute($flexibleEntity, $attribute)->setOption($option);
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
     * Finds an attribute option with value
     *
     * @param AbstractAttribute $attribute
     * @param string $value
     * @return AbstractAttributeOption
     * @throws \LogicException
     */

    private function findAttributeOptionWithValue(AbstractAttribute $attribute, $value)
    {
        /** @var $options \Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption[] */
        $options = $this->contactManager->getAttributeOptionRepository()->findBy(
            array('attribute' => $attribute)
        );

        $selectedOption = null;
        foreach ($options as $option) {
            if ($value == $option->getOptionValue()->getValue()) {
                return $option;
            }
        }

        throw new \LogicException(sprintf('Cannot find attribute option with value "%s"', $value));
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
        return $repository->findAttributeByCode($attributeCode);
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
