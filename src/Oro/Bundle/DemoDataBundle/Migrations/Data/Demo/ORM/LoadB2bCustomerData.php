<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone;

/**
 * Loads Business Customers
 */
class LoadB2bCustomerData extends AbstractDemoFixture implements DependentFixtureInterface
{
    /** @var array */
    protected $accountIds;

    /** @var int */
    protected $accountsCount;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadAccountData',
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadChannelData',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        $handle  = fopen($dictionaryDir . DIRECTORY_SEPARATOR . "accounts.csv", "r");
        $headers = fgetcsv($handle, 1000, ",");

        $companies          = [];
        $customersPersisted = 0;
        $channel            = $this->getChannel();
        $organization       = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        while (($data = fgetcsv($handle, 1000, ",")) !== false && $customersPersisted < 25) {
            $data = array_combine($headers, array_values($data));

            if (!isset($companies[$data['Company']])) {
                $customer = $this->createCustomer($organization, $data, $channel);

                $this->em->persist($customer);

                $companies[$data['Company']] = $data['Company'];
                $customersPersisted++;
            }
        }
        fclose($handle);

        $this->em->flush();
    }

    /**
     * @param Organization $organization
     * @param array        $data
     * @param Channel      $channel
     *
     * @return B2bCustomer
     */
    protected function createCustomer(Organization $organization, $data, Channel $channel = null)
    {
        $address  = new Address();
        $customer = new B2bCustomer();

        $customer->setName($data['Company']);
        $customer->setOwner($this->getRandomUserReference());
        $customer->setAccount($this->getAccount($organization));
        $customer->setOrganization($organization);

        $phone = new B2bCustomerPhone($data['TelephoneNumber']);
        $phone->setPrimary(true);
        $customer->addPhone($phone);

        $email = new B2bCustomerEmail($data['EmailAddress']);
        $email->setPrimary(true);
        $customer->addEmail($email);

        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);

        $address->setCountry($this->getCountryReference($data['Country']));
        $address->setRegion($this->getRegionReference($data['Country'], $data['State']));

        $customer->setShippingAddress($address);
        $customer->setBillingAddress(clone $address);

        if ($channel) {
            $customer->setDataChannel($channel);
        }

        return $customer;
    }

    /**
     * @param int $identifier
     *
     * @return Account
     */
    protected function getAccountReference($identifier)
    {
        return $this->em->getReference('OroAccountBundle:Account', $identifier);
    }

    /**
     * @return null|Channel
     */
    private function getChannel()
    {
        if ($this->hasReference('default_channel')) {
            return $this->getReference('default_channel');
        } else {
            return $this->em->getRepository('OroChannelBundle:Channel')->createQueryBuilder('c')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
        }
    }

    /**
     * @param Organization $organization
     * @return Account
     */
    private function getAccount(Organization $organization)
    {
        if (empty($this->accountIds)) {
            $this->accountIds = $this->loadAccountsIds($organization);
            shuffle($this->accountIds);
            $this->accountsCount = count($this->accountIds) - 1;
        }

        $random = rand(0, $this->accountsCount);

        return $this->getAccountReference($this->accountIds[$random]);
    }

    /**
     * @param Organization $organization
     * @return array
     */
    private function loadAccountsIds(Organization $organization)
    {
        $items = $this->em->createQueryBuilder()
            ->from('OroAccountBundle:Account', 'a')
            ->select('a.id')
            ->andWhere('a.organization = :organization')
            ->setParameter('organization', $organization)
            ->getQuery()
            ->getArrayResult();

        return array_map(
            function ($item) {
                return $item['id'];
            },
            $items
        );
    }
}
