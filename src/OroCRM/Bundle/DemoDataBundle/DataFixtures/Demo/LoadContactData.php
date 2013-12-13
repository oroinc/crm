<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures\Demo;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;

use OroCRM\Bundle\AccountBundle\Entity\Account;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\Source;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;

class LoadContactData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
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
     * @var EntityRepository
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

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->userManager = $container->get('oro_user.manager');
        $this->contactManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadContacts();
    }

    /**
     * @param ObjectManager $manager
     */
    protected function initSupportingEntities(ObjectManager $manager)
    {
        $this->users = $manager->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $manager->getRepository('OroAddressBundle:Country')->findAll();

        $this->accounts = $manager->getRepository('OroCRMAccountBundle:Account')->findAll();
        $this->contactGroups = $manager->getRepository('OroCRMContactBundle:Group')->findAll();
        $this->contactSources = $manager->getRepository('OroCRMContactBundle:Source')->findAll();
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
            $randomUser = count($this->users)-1;
            $randomContactGroup = count($this->contactGroups)-1;
            $randomContactSource = count($this->contactSources)-1;
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

                $group = $this->contactGroups[rand(0, $randomContactGroup)];
                $contact->addGroup($group);

                $user = $this->users[rand(0, $randomUser)];

                $contact->setAssignedTo($user);
                $contact->setReportsTo($contact);
                $contact->setOwner($user);

                $source = $this->contactSources[rand(0, $randomContactSource)];
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
     * @param  array   $data
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
