<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;

class LoadSalesBundleFixtures extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    public const CUSTOMER_NAME = 'b2bCustomer name';
    public const CHANNEL_TYPE = 'b2b';
    public const CHANNEL_NAME = 'b2b Channel';
    public const ACCOUNT_NAME = 'some account name';

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $tokenStorage->setToken(new OrganizationToken($this->getReference(LoadOrganization::ORGANIZATION)));
        $this->createChannel($manager);
        $this->createAccount($manager);
        $this->createContact($manager);
        $this->createB2bCustomer($manager);
        $this->createLead($manager);
        $this->createOpportunity($manager);
        $tokenStorage->setToken(null);
    }

    private function createAccount(ObjectManager $manager): void
    {
        $account = new Account();
        $account->setName(self::ACCOUNT_NAME);
        $account->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));

        $manager->persist($account);
        $manager->flush();

        $this->setReference('default_account', $account);
    }

    private function createContact(ObjectManager $manager): void
    {
        $contact = new Contact();
        $contact->setFirstName('John');
        $contact->setLastName('Doe');
        $contact->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));

        $manager->persist($contact);
        $manager->flush();

        $this->setReference('default_contact', $contact);
    }

    private function createB2bCustomer(ObjectManager $manager): void
    {
        $customer = new B2bCustomer();
        $account = $this->getReference('default_account');
        $customer->setAccount($account);
        $customer->setName(self::CUSTOMER_NAME);
        $customer->setDataChannel($this->getReference('default_channel'));
        $customer->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $customer->setBillingAddress($this->getBillingAddress($manager));
        $customer->setShippingAddress($this->getShippingAddress($manager));

        $manager->persist($customer);
        $manager->flush();

        $this->setReference('default_b2bcustomer', $customer);
        $this->setReference(
            'default_account_customer',
            $this->container->get('oro_sales.manager.account_customer')->getAccountCustomerByTarget($customer)
        );
    }

    private function createLead(ObjectManager $manager): void
    {
        $lead = new Lead();
        $lead->setName('Lead name');
        $lead->setFirstName('fname');
        $lead->setLastName('lname');
        $lead->setCustomerAssociation($this->getReference('default_account_customer'));
        $email = new LeadEmail('email@email.com');
        $email->setPrimary(true);
        $lead->addEmail($email);
        $lead->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));

        $lead2 = new Lead();
        $lead2->setName('Lead name 2');
        $lead2->setFirstName('fname 2');
        $lead2->setLastName('lname 2');
        $lead2->setCustomerAssociation($this->getReference('default_account_customer'));
        $email = new LeadEmail('email2@email.com');
        $email->setPrimary(true);
        $lead2->addEmail($email);
        $lead2->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));

        $lead3 = new Lead();
        $lead3->setName('Lead name 3');
        $lead3->setFirstName('fname 3');
        $lead3->setLastName('lname 3');
        $lead3->setCustomerAssociation($this->getReference('default_account_customer'));
        $email = new LeadEmail('email3@email.com');
        $email->setPrimary(true);
        $lead3->addEmail($email);
        $lead3->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));

        $manager->persist($lead);
        $manager->persist($lead2);
        $manager->persist($lead3);
        $manager->flush();

        $this->setReference('default_lead', $lead);
        $this->setReference('second_lead', $lead2);
        $this->setReference('third_lead', $lead3);
    }

    private function createOpportunity(ObjectManager $manager): void
    {
        $opportunity = new Opportunity();
        $opportunity->setName('opname');
        $opportunity->setCustomerAssociation($this->getReference('default_account_customer'));
        $budgetAmount = MultiCurrency::create(50.00, 'USD');
        $opportunity->setBudgetAmount($budgetAmount);
        $opportunity->setProbability(0.1);
        $opportunity->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));

        $manager->persist($opportunity);
        $manager->flush();

        $this->setReference('default_opportunity', $opportunity);
    }

    private function createChannel(ObjectManager $manager): void
    {
        $channel = $this->container->get('oro_channel.builder.factory')
            ->createBuilder()
            ->setName(self::CHANNEL_NAME)
            ->setChannelType(self::CHANNEL_TYPE)
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setOwner($this->getReference(LoadOrganization::ORGANIZATION))
            ->setEntities()
            ->getChannel();

        $manager->persist($channel);
        $manager->flush();

        $this->setReference('default_channel', $channel);
    }

    private function getBillingAddress(ObjectManager $manager): Address
    {
        $address = new Address();
        $address->setCountry($this->getCounty($manager));
        $address->setStreet('1215 Caldwell Road');
        $address->setCity('Rochester');
        $address->setPostalCode('14608');
        $address->setRegionText('Arizona1');
        $address->setOrganization('Test Org');

        return $address;
    }

    private function getShippingAddress(ObjectManager $manager): Address
    {
        $address = new Address();
        $address->setCountry($this->getCounty($manager));
        $address->setStreet('1215 Caldwell Road');
        $address->setCity('Rochester');
        $address->setPostalCode('14608');
        $address->setRegionText('Arizona1');
        $address->setOrganization('Test Org');

        return $address;
    }

    private function getCounty(ObjectManager $manager): Country
    {
        return $manager->find(Country::class, 'IM');
    }
}
