<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\AccountBundle\Entity\Account;

class LoadAccountData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /** @var  EntityManager */
    protected $em;

    /**
     * @var User[]
     */
    protected $users;

    /**
     * @var Country[]
     */
    protected $countries;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadAccounts();
    }

    /**
     * @param ObjectManager $manager
     */
    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }

        $this->users = $manager->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $manager->getRepository('OroAddressBundle:Country')->findAll();
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
            $randomUser = count($this->users)-1;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $data = array_combine($headers, array_values($data));
                if (!array_key_exists($data['Company'], $companies)) {
                    $account = $this->createAccount($data);
                    $account->setOwner($this->users[rand(0, $randomUser)]);

                    $this->persist($this->em, $account);

                    $companies[$data['Company']] = $data['Company'];
                }
            }
            fclose($handle);
        }
        $this->flush($this->em);
    }

    /**
     * Create an Account
     *
     * @param  array   $data
     * @return Account
     */
    private function createAccount(array $data)
    {
        /** @var $account Account */
        $account = new Account();

        $account->setName($data['Company']);

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
            $address->setRegion($region->first());
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
}
