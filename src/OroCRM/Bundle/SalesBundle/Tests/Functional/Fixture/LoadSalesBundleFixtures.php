<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadEmail;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\SalesFunnel;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadSalesBundleFixtures extends AbstractFixture implements ContainerAwareInterface
{
    const CUSTOMER_NAME = 'b2bCustomer name';
    const CHANNEL_TYPE  = 'b2b';
    const CHANNEL_NAME  = 'b2b Channel';
    const ACCOUNT_NAME  = 'some account name';

    /** @var ObjectManager */
    protected $em;

    /** @var BuilderFactory */
    protected $factory;

    /** @var Channel */
    protected $channel;

    /** @var User */
    protected $user;

    /** @var Organization */
    protected $organization;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->factory = $container->get('orocrm_channel.builder.factory');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $this->createChannel();
        $this->createAccount();
        $this->createContact();
        $this->createB2bCustomer();
        $this->createLead();
        $this->createOpportunity();
        $this->createSalesFunnelByLead();
        $this->createSalesFunnelByOpportunity();
    }

    protected function createAccount()
    {
        $account = new Account();
        $account->setName(self::ACCOUNT_NAME);
        $account->setOrganization($this->organization);

        $this->em->persist($account);
        $this->em->flush();

        $this->setReference('default_account', $account);

        return $this;
    }

    protected function createContact()
    {
        $contact = new Contact();
        $contact->setFirstName('John');
        $contact->setLastName('Doe');
        $contact->setOrganization($this->organization);

        $this->em->persist($contact);
        $this->em->flush();

        $this->setReference('default_contact', $contact);

        return $this;
    }

    protected function createB2bCustomer()
    {
        $customer = new B2bCustomer();
        $customer->setAccount($this->getReference('default_account'));
        $customer->setName(self::CUSTOMER_NAME);
        $customer->setDataChannel($this->getReference('default_channel'));
        $customer->setOrganization($this->organization);

        $this->em->persist($customer);
        $this->em->flush();

        $this->setReference('default_b2bcustomer', $customer);

        return $this;
    }

    protected function createLead()
    {
        $lead = new Lead();
        $lead->setDataChannel($this->getReference('default_channel'));
        $lead->setName('Lead name');
        $lead->setFirstName('fname');
        $lead->setLastName('lname');
        $lead->setCustomer($this->getReference('default_b2bcustomer'));
        $email = new LeadEmail('email@email.com');
        $email->setPrimary(true);
        $lead->addEmail($email);
        $lead->setOrganization($this->organization);

        $lead2 = new Lead();
        $lead2->setDataChannel($this->getReference('default_channel'));
        $lead2->setName('Lead name 2');
        $lead2->setFirstName('fname 2');
        $lead2->setLastName('lname 2');
        $lead2->setCustomer($this->getReference('default_b2bcustomer'));
        $email = new LeadEmail('email2@email.com');
        $email->setPrimary(true);
        $lead2->addEmail($email);
        $lead2->setOrganization($this->organization);

        $lead3 = new Lead();
        $lead3->setDataChannel($this->getReference('default_channel'));
        $lead3->setName('Lead name 3');
        $lead3->setFirstName('fname 3');
        $lead3->setLastName('lname 3');
        $lead3->setCustomer($this->getReference('default_b2bcustomer'));
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

        return $this;
    }

    protected function createOpportunity()
    {
        $opportunity = new Opportunity();
        $opportunity->setName('opname');
        $opportunity->setCustomer($this->getReference('default_b2bcustomer'));
        $opportunity->setDataChannel($this->getReference('default_channel'));
        $opportunity->setBudgetAmount(50.00);
        $opportunity->setProbability(10);
        $opportunity->setOrganization($this->organization);

        $this->em->persist($opportunity);
        $this->em->flush();

        $this->setReference('default_opportunity', $opportunity);

        return $this;
    }

    protected function createSalesFunnelByLead()
    {
        $date = new \DateTime('now');

        $salesFunnel = new SalesFunnel();
        $salesFunnel->setDataChannel($this->getReference('default_channel'));
        $salesFunnel->setLead($this->getReference('default_lead'));
        $salesFunnel->setOwner($this->getUser());
        $salesFunnel->setStartDate($date);
        $salesFunnel->setOrganization($this->organization);

        $this->em->persist($salesFunnel);
        $this->em->flush();
    }

    protected function createSalesFunnelByOpportunity()
    {
        $date = new \DateTime('now');

        $salesFunnel = new SalesFunnel();
        $salesFunnel->setDataChannel($this->getReference('default_channel'));
        $salesFunnel->setOpportunity($this->getReference('default_opportunity'));
        $salesFunnel->setOwner($this->getUser());
        $salesFunnel->setStartDate($date);
        $salesFunnel->setOrganization($this->organization);

        $this->em->persist($salesFunnel);
        $this->em->flush();
    }

    /**
     * @return Channel
     */
    protected function createChannel()
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

        return $this;
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        if (empty($this->user)) {
            $this->user = $this->em->getRepository('OroUserBundle:User')->findOneBy(['username' => 'admin']);
        }

        return $this->user;
    }
}
