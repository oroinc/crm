<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class LoadOpportunitiesData extends AbstractDemoFixture implements DependentFixtureInterface
{
    /** @var Contact[] */
    protected $contacts;

    /** @var  B2bCustomer[] */
    protected $b2bCustomers;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadLeadsData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadB2bCustomerData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadChannelData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities();
        $this->loadOpportunities();
    }

    protected function initSupportingEntities()
    {
        /** @var EntityRepository $repo */
        $this->contacts     = $this->em->getRepository('OroCRMContactBundle:Contact')->findAll();
        $this->b2bCustomers = $this->em->getRepository('OroCRMSalesBundle:B2bCustomer')->findAll();
    }

    public function loadOpportunities()
    {
        for ($i = 0; $i < 50; $i++) {
            $user = $this->getRandomUserReference();

            $this->setSecurityContext($user);
            $contact     = $this->contacts[array_rand($this->contacts)];
            $customer    = $this->b2bCustomers[array_rand($this->b2bCustomers)];
            $opportunity = $this->createOpportunity($contact, $customer, $user);
            $this->em->persist($opportunity);
        }

        $this->em->flush();
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $securityContext = $this->container->get('security.context');
        $token           = new UsernamePasswordToken($user, uniqid('username'), 'main');
        $securityContext->setToken($token);
    }

    /**
     * @param Contact     $contact
     * @param B2bCustomer $customer
     * @param User        $user
     *
     * @return Opportunity
     */
    protected function createOpportunity($contact, $customer, $user)
    {
        $opportunity = new Opportunity();
        $dataChannel = $this->getReference('default_channel');
        $opportunity->setName($contact->getFirstName() . ' ' . $contact->getLastName());
        $opportunity->setContact($contact);
        $opportunity->setOwner($user);
        $opportunity->setCustomer($customer);
        $opportunity->setDataChannel($dataChannel);

        return $opportunity;
    }
}
