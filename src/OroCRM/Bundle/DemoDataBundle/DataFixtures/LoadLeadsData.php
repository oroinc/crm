<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Faker\Factory;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LoadLeadsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var User[]
     */
    protected $users;

    /**
     * @var Country[]
     */
    protected $countries;

    /** @var  WorkflowManager */
    protected $workflowManager;

    /** @var  EntityManager */
    protected $em;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->userManager = $container->get('oro_user.manager');
        $this->workflowManager = $container->get('oro_workflow.manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities();
        $this->loadLeads();
    }

    protected function initSupportingEntities()
    {
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $userStorageManager = $this->userManager->getStorageManager();
        $this->users = $userStorageManager->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $userStorageManager->getRepository('OroAddressBundle:Country')->findAll();
    }

    public function loadLeads()
    {
        $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR. "leads.csv", "r");
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $user = $this->users[rand(0, count($this->users)-1)];
                $this->setSecurityContext($user);

                $data = array_combine($headers, array_values($data));

                $lead = $this->createLead($data);

                $this->persist($this->em, $lead);

                $workFlow = $this->workflowManager->startWorkflow('sales_lead', $lead, 'qualify');
                if ((bool)rand(0, 1)) {
                    /** @var WorkflowItem $salesFlow */
                    $salesFlow = $workFlow->getResult()->get('workflowItem');
                    $this->transit(
                        $this->workflowManager,
                        $salesFlow,
                        'close',
                        array(
                            'budget_amount' => rand(10, 10000),
                            'customer_need' => rand(10, 10000),
                            'proposed_solution' => rand(10, 10000),
                            'probability' => round(rand(50, 85) / 100.00, 2)
                        )
                    );
                    if ((bool)rand(0, 1)) {
                        $reason = $this->em->find('OroCRMSalesBundle:OpportunityCloseReason', 'won');
                        $this->transit(
                            $this->workflowManager,
                            $salesFlow,
                            'close_as_won',
                            array(
                                'close_reason' => $reason,
                                'close_revenue' => rand(100, 1000),
                                'close_date' => new \DateTime('now'),
                                'probability' => 1
                            )
                        );
                    } elseif ((bool)rand(0, 1)) {
                        $reason = $this->em->find('OroCRMSalesBundle:OpportunityCloseReason', 'cancelled');
                        $this->transit(
                            $this->workflowManager,
                            $salesFlow,
                            'close_as_lost',
                            array(
                                'close_reason' => $reason,
                                'close_revenue' => rand(100, 1000),
                                'close_date' => new \DateTime('now'),
                                'probability' => 0.0
                            )
                        );
                    }
                }
            }

            $this->flush($this->em);
            fclose($handle);
        }
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $securityContext = $this->container->get('security.context');
        $token = new UsernamePasswordToken($user, $user->getName(), 'main');
        $securityContext->setToken($token);
    }
    /**
     * @param array $data
     * @return Lead
     */
    protected function createLead(array $data)
    {
        $user = $this->users[rand(0, count($this->users)-1)];
        $lead = new Lead();
        $defaultStatus = $this->em->find('OroCRMSalesBundle:LeadStatus', 'new');
        $lead->setStatus($defaultStatus);
        $lead->setName($data['Company']);
        $lead->setFirstName($data['GivenName']);
        $lead->setLastName($data['Surname']);
        $lead->setEmail($data['EmailAddress']);
        $lead->setPhoneNumber($data['TelephoneNumber']);
        $lead->setOwner($user);
        /** @var Address $address */
        $address = new Address();
        $address->setLabel('Primary Address');
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);

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

        $lead->setAddress($address);

        return $lead;
    }

    /**
     * @param WorkflowManager $workflowManager
     * @param WorkflowItem $workflowItem
     * @param string $step
     * @param array $data
     */
    protected function transit($workflowManager, $workflowItem, $step, array $data)
    {
        foreach ($data as $key => $value) {
            $workflowItem->getData()->set($key, $value);
        }
        $workflowManager->transit($workflowItem, $step);
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
        return 300;
    }
}
