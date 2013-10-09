<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;


use OroCRM\Bundle\DemoDataBundle\DataFixtures\AbstractFlexibleFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\UserBundle\Entity\User;

class LoadAccountData extends AbstractFlexibleFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var FlexibleManager
     */
    protected $accountManager;

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

        $this->accountManager = $container->get('orocrm_account.account.manager.flexible');
        $this->userManager = $container->get('oro_user.manager');
        $this->tagManager = $container->get('oro_tag.tag.manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities();
        $this->loadAccounts();
    }

    protected function initSupportingEntities()
    {
        $userStorageManager = $this->userManager->getStorageManager();
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $this->users = $userStorageManager->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $userStorageManager->getRepository('OroAddressBundle:Country')->findAll();
        $this->tagsRepository = $entityManager->getRepository('OroTagBundle:Tag');
    }

    /**
     * Load Accounts
     *
     * @return void
     */
    public function loadAccounts()
    {
         $companies = array();

        $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR. "accounts.csv", "r");
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                if (!array_key_exists($data['Company'], $companies)) {
                    $account = $this->createAccount($data);
                    $user = $this->users[rand(0, count($this->users)-1)];
                    $account->setOwner($user);

                    $this->persist($this->accountManager, $account);

                    $companies[$data['Company']] = $data['Company'];
                }
            }
            fclose($handle);
        }
        $this->flush($this->accountManager);
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

        $account->setName($data['Company']);

        $this->setFlexibleAttributeValue($this->accountManager, $account, 'phone', $data['TelephoneNumber']);
        $this->setFlexibleAttributeValue($this->accountManager, $account, 'email', $data['EmailAddress']);
        $this->setFlexibleAttributeValue($this->accountManager, $account, 'website', $data['Domain']);

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

    public function getOrder()
    {
        return 200;
    }
}
