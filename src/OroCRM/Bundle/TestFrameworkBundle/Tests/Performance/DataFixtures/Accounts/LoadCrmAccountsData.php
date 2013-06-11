<?php

namespace OroCRM\Bundle\TestFrameworkBundle\Tests\DataFixtures;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;

use Oro\Bundle\FlexibleEntityBundle\Entity\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\PhoneType;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\EmailType;
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
    const FLUSH_MAX = 200;

    /** @var array Lead Sources */
    protected $leadSource = array('other', 'call', 'TV', 'website');
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

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->accountManager = $container->get('orocrm_account.account.manager.flexible');
        $this->accountRepository = $this->accountManager->getFlexibleRepository();

        $this->contactManager = $container->get('orocrm_contact.manager.flexible');
        $this->contactRepository = $this->contactManager->getFlexibleRepository();
        $this->groups = $this->contactManager->getStorageManager()->getRepository('OroCRMContactBundle:Group')->findAll();

        $this->userManager = $container->get('oro_user.manager');
        $this->userRepository = $this->userManager->getFlexibleRepository();
        $this->users = $this->userManager->getStorageManager()->getRepository('OroUserBundle:User')->findAll();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->countryRepository = $manager->getRepository('OroAddressBundle:Country');

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
        $arrResult = array();
        $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . "data.csv", "r");
        if ( $handle ) {
            $j=$i=0;
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            echo "\nLoading...\n";
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                $account = $this->createAccount($data);
                $contact = $this->createContact($data);
                $contact->addAccount($account);
                $group = $this->groups[rand(0, count($this->groups)-1)];
                $contact->addGroup($group);
                $user = $this->users[rand(0, count($this->users)-1)];
                $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'assigned_to', $user);
                $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'reports_to', $contact);
                $leadSource = $this->leadSource[rand(0, count($this->leadSource)-1)];
                $this->setFlexibleAttributeValueOption($this->contactRepository, $contact, 'lead_source', $leadSource);


                $this->persist($this->accountManager, $account);
                $this->persist($this->contactManager, $contact);

                $i++;
                $j++;
                if ($i >= self::FLUSH_MAX) {
                    $this->flush($this->accountManager);
                    $this->flush($this->contactManager);
                    echo ">> {$j}\n";
                    $i = 0;
                }
            }
            fclose($handle);
        }
        $this->flush($this->accountManager);
        $this->flush($this->contactManager);

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

        $phone = new Collection;
        $phone->setData($data['TelephoneNumber']);
        $phone->setType(rand(PhoneType::TYPE_OFFICE, PhoneType::TYPE_CELL));
        $this->addFlexibleAttributeCollection($this->accountRepository, $account, 'phones', $phone);
        $this->setFlexibleAttributeValue($this->accountRepository, $account, 'email', $data['EmailAddress']);
        $this->setFlexibleAttributeValue($this->accountRepository, $account, 'website', $data['Domain']);

        $address = new Address();
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);

        /** @var Country $country */
        $country = $this->countryRepository->find($data['Country']);
        $idRegion = $data['State'];
        $idRegion = 'AL';
        /** @var ArrayCollection $regions */
        $regions = $country->getRegions();

        $region = $regions->filter(
            function ($a) use ($idRegion) {
                return $a->getCode() == $idRegion;
            }
        );

        $address->setCountry($country);
        $address->setState($region->get(0));

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

        $date = date('m/d/y', strtotime($data['Birthday']));
        $date = new \DateTime($date);

        $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'birthday', $date);

        $phone = new Collection;
        $phone->setData($data['TelephoneNumber']);
        $phone->setType(rand(PhoneType::TYPE_OFFICE, PhoneType::TYPE_CELL));
        $this->addFlexibleAttributeCollection($this->contactRepository, $contact, 'phones', $phone);

        $email = new Collection;
        $email->setData($data['EmailAddress']);
        $email->setType(rand(EmailType::TYPE_CORPORATE, EmailType::TYPE_PERSONAL));
        $this->addFlexibleAttributeCollection($this->contactRepository, $contact, 'emails', $phone);

        $address = new Address();
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);

        /** @var Country $country */
        $country = $this->countryRepository->find($data['Country']);
        $idRegion = $data['State'];
        $idRegion = 'AL';
        /** @var ArrayCollection $regions */
        $regions = $country->getRegions();

        $region = $regions->filter(
            function ($a) use ($idRegion) {
                return $a->getCode() == $idRegion;
            }
        );

        $address->setCountry($country);
        $address->setState($region->get(0));

        $this->setFlexibleAttributeValue($this->contactRepository, $contact, 'address', $address);

        return $contact;
    }

    /**
     * Sets a flexible attribute value
     *
     * @param FlexibleEntityRepository $repository
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @return void
     * @throws \LogicException
     */
    private function addFlexibleAttributeCollection(FlexibleEntityRepository $repository, AbstractFlexible $flexibleEntity, $attributeCode, $value)
    {
        if ($attribute = $this->findAttribute($repository, $attributeCode)) {
            $this->getFlexibleValueForAttribute($flexibleEntity, $attribute)->getCollection()->add($value);
        } else {
            throw new \LogicException(sprintf('Cannot set value, attribute "%s" is missing', $attributeCode));
        }
    }

    /**
     * Sets a flexible attribute value
     *
     * @param FlexibleEntityRepository $repository
     * @param AbstractFlexible $flexibleEntity
     * @param string $attributeCode
     * @param string $value
     * @return void
     * @throws \LogicException
     */
    private function setFlexibleAttributeValue(FlexibleEntityRepository $repository, AbstractFlexible $flexibleEntity, $attributeCode, $value)
    {
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
     * @return void
     * @throws \LogicException
     */
    private function setFlexibleAttributeValueOption(FlexibleEntityRepository $repository, AbstractFlexible $flexibleEntity, $attributeCode, $value)
    {
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
     * @return void
     */
    private function persist($manager, $object)
    {
        $manager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     *
     * @param mixed $manager
     * @return void
     */
    private function flush($manager)
    {
        $manager->getStorageManager()->flush();
    }
}
