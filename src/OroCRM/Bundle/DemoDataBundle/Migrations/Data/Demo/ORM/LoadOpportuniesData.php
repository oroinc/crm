<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

class LoadOpportunitiesData extends AbstractDemoFixture implements DependentFixtureInterface
{
    /** @var Contact[] */
    protected $contacts;

    /** @var  B2bCustomer[] */
    protected $b2bCustomers;

    /** @var Organization */
    protected $organization;

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
        $this->organization = $this->getReference('default_organization');
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
        $token = new UsernamePasswordOrganizationToken($user, $user->getUsername(), 'main', $this->organization);
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
        $opportunity->setOrganization($this->organization);
        $opportunity->setCustomer($customer);
        $opportunity->setDataChannel($dataChannel);

        $opportunityStatuses = ['in_progress', 'lost', 'needs_analysis', 'won'];
        $statusName = $opportunityStatuses[array_rand($opportunityStatuses)];
        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $opportunity->setStatus($this->em->getReference($enumClass, $statusName));
        
        return $opportunity;
    }
}
