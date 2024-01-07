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
 * Loads B2B customers.
 */
class LoadB2bCustomerData extends AbstractDemoFixture implements DependentFixtureInterface
{
    private ?array $accountIds = null;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            LoadUsersData::class,
            LoadAccountData::class,
            LoadChannelData::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        $handle = fopen($dictionaryDir . DIRECTORY_SEPARATOR . 'accounts.csv', 'r');
        $headers = fgetcsv($handle, 1000, ',');

        $companies = [];
        $customersPersisted = 0;
        $channel = $this->getChannel();
        $organization = $manager->getRepository(Organization::class)->getFirst();

        while (($data = fgetcsv($handle, 1000, ',')) !== false && $customersPersisted < 25) {
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

    private function createCustomer(Organization $organization, array $data, ?Channel $channel): B2bCustomer
    {
        $address = new Address();
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

    private function getAccountReference(int $id): Account
    {
        return $this->em->getReference(Account::class, $id);
    }

    private function getChannel(): ?Channel
    {
        if ($this->hasReference('default_channel')) {
            return $this->getReference('default_channel');
        }

        return $this->em->getRepository(Channel::class)->createQueryBuilder('c')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    private function getAccount(Organization $organization): Account
    {
        if (null === $this->accountIds) {
            $this->accountIds = $this->loadAccountsIds($organization);
            shuffle($this->accountIds);
        }

        return $this->getAccountReference($this->accountIds[rand(0, \count($this->accountIds) - 1)]);
    }

    private function loadAccountsIds(Organization $organization): array
    {
        $items = $this->em->createQueryBuilder()
            ->from(Account::class, 'a')
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
