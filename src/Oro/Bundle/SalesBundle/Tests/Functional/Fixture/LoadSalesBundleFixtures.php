<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoadSalesBundleFixtures extends AbstractFixture implements ContainerAwareInterface
{
    public const CUSTOMER_NAME = 'b2bCustomer name';
    public const CHANNEL_TYPE  = 'b2b';
    public const CHANNEL_NAME  = 'b2b Channel';
    public const ACCOUNT_NAME  = 'some account name';

    protected ObjectManager $em;
    private BuilderFactory $factory;
    private User $user;
    private Organization $organization;
    private AccountCustomerManager $accountCustomerManager;
    private TokenStorageInterface $securityToken;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->factory = $container->get('oro_channel.builder.factory');
        $this->accountCustomerManager = $container->get('oro_sales.manager.account_customer');
        $this->securityToken = $container->get('security.token_storage');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        $this->organization = $manager->getRepository(Organization::class)->getFirst();
        $this->securityToken->setToken(new OrganizationToken($this->organization));

        $this->createChannel();
        $this->createAccount();
        $this->createContact();
        $this->createB2bCustomer();
        $this->createLead();
        $this->createOpportunity();
    }

    private function createAccount(): void
    {
        $account = new Account();
        $account->setName(self::ACCOUNT_NAME);
        $account->setOrganization($this->organization);

        $this->em->persist($account);
        $this->em->flush();

        $this->setReference('default_account', $account);
    }

    private function createContact(): void
    {
        $contact = new Contact();
        $contact->setFirstName('John');
        $contact->setLastName('Doe');
        $contact->setOrganization($this->organization);

        $this->em->persist($contact);
        $this->em->flush();

        $this->setReference('default_contact', $contact);
    }

    private function createB2bCustomer(): void
    {
        $customer = new B2bCustomer();
        $account  = $this->getReference('default_account');
        $customer->setAccount($account);
        $customer->setName(self::CUSTOMER_NAME);
        $customer->setDataChannel($this->getReference('default_channel'));
        $customer->setOrganization($this->organization);
        $customer->setBillingAddress($this->getBillingAddress());
        $customer->setShippingAddress($this->getShippingAddress());

        $this->em->persist($customer);
        $this->em->flush();

        $this->setReference('default_b2bcustomer', $customer);
        $accountCustomer = $this->accountCustomerManager->getAccountCustomerByTarget($customer);
        $this->setReference('default_account_customer', $accountCustomer);
    }

    private function createLead(): void
    {
        $lead = new Lead();
        $lead->setName('Lead name');
        $lead->setFirstName('fname');
        $lead->setLastName('lname');
        $lead->setCustomerAssociation($this->getReference('default_account_customer'));
        $email = new LeadEmail('email@email.com');
        $email->setPrimary(true);
        $lead->addEmail($email);
        $lead->setOrganization($this->organization);

        $lead2 = new Lead();
        $lead2->setName('Lead name 2');
        $lead2->setFirstName('fname 2');
        $lead2->setLastName('lname 2');
        $lead2->setCustomerAssociation($this->getReference('default_account_customer'));
        $email = new LeadEmail('email2@email.com');
        $email->setPrimary(true);
        $lead2->addEmail($email);
        $lead2->setOrganization($this->organization);

        $lead3 = new Lead();
        $lead3->setName('Lead name 3');
        $lead3->setFirstName('fname 3');
        $lead3->setLastName('lname 3');
        $lead3->setCustomerAssociation($this->getReference('default_account_customer'));
        $email = new LeadEmail('email3@email.com');
        $email->setPrimary(true);
        $lead3->addEmail($email);
        $lead3->setOrganization($this->organization);

        $this->em->persist($lead);
        $this->em->persist($lead2);
        $this->em->persist($lead3);
        $this->em->flush();

        $this->setReference('default_lead', $lead);
        $this->setReference('second_lead', $lead2);
        $this->setReference('third_lead', $lead3);
    }

    private function createOpportunity(): void
    {
        $opportunity = new Opportunity();
        $opportunity->setName('opname');
        $opportunity->setCustomerAssociation($this->getReference('default_account_customer'));
        $budgetAmount = MultiCurrency::create(50.00, 'USD');
        $opportunity->setBudgetAmount($budgetAmount);
        $opportunity->setProbability(0.1);
        $opportunity->setOrganization($this->organization);

        $this->em->persist($opportunity);
        $this->em->flush();

        $this->setReference('default_opportunity', $opportunity);
    }

    private function createChannel(): void
    {
        $channel = $this
            ->factory
            ->createBuilder()
            ->setName(self::CHANNEL_NAME)
            ->setChannelType(self::CHANNEL_TYPE)
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setOwner($this->organization)
            ->setEntities()
            ->getChannel();

        $this->em->persist($channel);
        $this->em->flush();

        $this->setReference('default_channel', $channel);
    }

    private function getUser(): User
    {
        if (empty($this->user)) {
            $this->user = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        }

        return $this->user;
    }

    private function getBillingAddress(): Address
    {
        $address = new Address();
        $address->setCountry($this->getCounty())
            ->setStreet('1215 Caldwell Road')
            ->setCity('Rochester')
            ->setPostalCode('14608')
            ->setRegionText('Arizona1')
            ->setOrganization('Test Org');

        return $address;
    }

    private function getShippingAddress(): Address
    {
        $address = new Address();
        $address->setCountry($this->getCounty())
            ->setStreet('1215 Caldwell Road')
            ->setCity('Rochester')
            ->setPostalCode('14608')
            ->setRegionText('Arizona1')
            ->setOrganization('Test Org');

        return $address;
    }

    private function getCounty(): Country
    {
        return $this->em->find(Country::class, 'IM');
    }
}
