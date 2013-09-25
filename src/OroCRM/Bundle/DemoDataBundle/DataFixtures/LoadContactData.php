<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use OroCRM\Bundle\DemoDataBundle\DataFixtures\AbstractFlexibleFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Source;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class LoadContactData extends AbstractFlexibleFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Account[]
     */
    protected $accounts;

    /**
     * @var FlexibleEntityRepository
     */
    protected $accountRepository;

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
     * @var User[]
     */
    protected $users;

    /**
     * @var Country[]
     */
    protected $countries;

    /**
     * @var Group[]
     */
    protected $contactGroups;

    /**
     * @var Source[]
     */
    protected $contactSources;

    /**
     * @var EntityManager
     */
    protected $contactManager;

    /**
     * @var EntityRepository
     */
    protected $contactRepository;

    /** @var  TagManager */
    protected $tagManager;

    /** @var EntityRepository */
    protected $tagsRepository;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->userManager = $container->get('oro_user.manager');
        $this->tagManager = $container->get('oro_tag.tag.manager');
        $this->contactManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities();
        $this->loadContacts();
    }

    protected function initSupportingEntities()
    {
        $userStorageManager = $this->userManager->getStorageManager();
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $this->users = $userStorageManager->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $userStorageManager->getRepository('OroAddressBundle:Country')->findAll();


        $this->accounts = $this->contactManager->getRepository('OroCRMAccountBundle:Account')->findAll();
        $this->contactGroups = $this->contactManager->getRepository('OroCRMContactBundle:Group')->findAll();
        $this->contactSources = $this->contactManager->getRepository('OroCRMContactBundle:Source')->findAll();

        $this->tagsRepository = $entityManager->getRepository('OroTagBundle:Tag');
    }


    /**
     * Load Contacts
     *
     * @return void
     */
    public function loadContacts()
    {
        $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR. "accounts.csv", "r");
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                //find accounts
                $company = $data['Company'];

                $account = array_filter(
                    $this->accounts,
                    function (Account $a) use ($company) {
                        return $a->getName() == $company;
                    }
                );
                $account = reset($account);
                $contact = $this->createContact($data);

                /** @var Account $account */
                $contact->addAccount($account);

                $group = $this->contactGroups[rand(0, count($this->contactGroups)-1)];
                $contact->addGroup($group);

                $user = $this->users[rand(0, count($this->users)-1)];

                $contact->setAssignedTo($user);
                $contact->setReportsTo($contact);
                $contact->setOwner($user);

                $source = $this->contactSources[rand(0, count($this->contactSources)-1)];
                $contact->setSource($source);
                $account->setDefaultContact($contact);

                $this->persist($this->contactManager, $contact);

                $this->persist($this->contactManager, $account);
            }

            $this->flush($this->contactManager);
            fclose($handle);
        }
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
        $address->setLabel('Primary Address');
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
     * Persist object
     *
     * @param mixed $manager
     * @param mixed $object
     */
    private function persist($manager, $object)
    {
        $manager->persist($object);
    }

    /**
     * Flush objects
     *
     * @param mixed $manager
     */
    private function flush($manager)
    {
        $manager->flush();
    }

    public function getOrder()
    {
        return 210;
    }
}
