<?php

namespace OroCRM\Bundle\ReportBundle\Tests\Functional\DataFixtures;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSet;
use Oro\Bundle\EntityConfigBundle\Entity\OptionSetRelation;

use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

class LoadLeadsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    const FLUSH_MAX = 50;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var User[]
     */
    protected $users;

    /**
     * @var Country[]
     */
    protected $countries;

    /** @var WorkflowManager */
    protected $workflowManager;

    /** @var  EntityManager */
    protected $em;

    /** @var  ConfigManager */
    protected $configManager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->workflowManager = $container->get('oro_workflow.manager');
        $this->configManager = $container->get('oro_entity_config.config_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadLeads();
        $this->loadSources();
    }

    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }

        $this->users = $this->em->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $this->em->getRepository('OroAddressBundle:Country')->findAll();
    }

    public function loadSources()
    {
        // TODO: Use cache manager instead of manual entity extracting (see git history)
        // TODO: https://magecore.atlassian.net/browse/BAP-2706
        $entityConfigModel = $this->em->getRepository(EntityConfigModel::ENTITY_NAME)->findOneBy(
            array('className' => 'OroCRM\Bundle\SalesBundle\Entity\Lead')
        );
        $configFieldModel = $this->em->getRepository(FieldConfigModel::ENTITY_NAME)->findOneBy(
            array(
                'entity'    => $entityConfigModel,
                'fieldName' => 'extend_source'
            )
        );

        /** @var OptionSet[] $sources */
        $sources = $configFieldModel->getOptions()->toArray();
        $randomSource = count($sources)-1;

        $leads = $this->em->getRepository('OroCRMSalesBundle:Lead')->findAll();

        foreach ($leads as $lead) {
            /** @var Lead $lead */
            $source = $sources[mt_rand(0, $randomSource)];
            $optionSetRelation = new OptionSetRelation();
            $optionSetRelation->setData(
                null,
                $lead->getId(),
                $configFieldModel,
                $source
            );
            $this->persist($this->em, $optionSetRelation);
        }
        $this->flush($this->em);
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
            $randomUser = count($this->users) - 1;
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $user = $this->users[mt_rand(0, $randomUser)];
                $this->setSecurityContext($user);

                $data = array_combine($headers, array_values($data));

                $lead = $this->createLead($data, $user);
                $this->persist($this->em, $lead);

                $this->loadSalesFlows($lead);

                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $this->flush($this->em);
                }
            }

            $this->flush($this->em);
            fclose($handle);
        }
    }

    /**
     * @param Lead $lead
     */
    protected function loadSalesFlows(Lead $lead)
    {
        $leadWorkflowItem = $this->workflowManager->startWorkflow(
            'b2b_flow_lead',
            $lead,
            'qualify',
            array(
                'opportunity_name' => $lead->getName(),
                'company_name' => $lead->getCompanyName(),
                'account' => $lead->getAccount(),
            )
        );
        if ($this->getRandomBoolean()) {
            /** @var Opportunity $opportunity */
            $opportunity = $leadWorkflowItem->getResult()->get('opportunity');
            $salesFlowItem = $this->workflowManager->startWorkflow(
                'b2b_flow_sales',
                $opportunity,
                'develop',
                array(
                    'budget_amount' => mt_rand(10, 10000),
                    'customer_need' => mt_rand(10, 10000),
                    'proposed_solution' => mt_rand(10, 10000),
                    'probability' => round(mt_rand(50, 85) / 100.00, 2)
                )
            );

            if ($this->getRandomBoolean()) {
                if ($this->getRandomBoolean()) {
                    $this->transit(
                        $this->workflowManager,
                        $salesFlowItem,
                        'close_as_won',
                        array(
                            'close_revenue' => mt_rand(100, 1000),
                            'close_date' => new \DateTime('now'),
                        )
                    );
                } else {
                    $this->transit(
                        $this->workflowManager,
                        $salesFlowItem,
                        'close_as_lost',
                        array(
                            'close_reason_name' => 'cancelled',
                            'close_revenue' => mt_rand(100, 1000),
                            'close_date' => new \DateTime('now'),
                        )
                    );
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function getRandomBoolean()
    {
        return (bool) mt_rand(0, 1);
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $securityContext = $this->container->get('security.context');
        $token = new UsernamePasswordToken($user, $user->getUsername(), 'main');
        $securityContext->setToken($token);
    }
    /**
     * @param  array $data
     * @param User $user
     *
     * @return Lead
     */
    protected function createLead(array $data, $user)
    {
        $lead = new Lead();
        /** @var LeadStatus $defaultStatus */
        $defaultStatus = $this->em->find('OroCRMSalesBundle:LeadStatus', 'new');
        $lead->setStatus($defaultStatus);
        $lead->setName($data['Company']);
        $lead->setFirstName($data['GivenName']);
        $lead->setLastName($data['Surname']);
        $lead->setEmail($data['EmailAddress']);
        $lead->setPhoneNumber($data['TelephoneNumber']);
        $lead->setCompanyName($data['Company']);
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
            $address->setRegion($region->first());
        }

        $lead->setAddress($address);

        return $lead;
    }

    /**
     * @param WorkflowManager $workflowManager
     * @param WorkflowItem    $workflowItem
     * @param string          $transition
     * @param array           $data
     */
    protected function transit($workflowManager, $workflowItem, $transition, array $data)
    {
        foreach ($data as $key => $value) {
            $workflowItem->getData()->set($key, $value);
        }

        $workflow = $workflowManager->getWorkflow($workflowItem);
        /** @var EntityManager $em */
        $workflow->transit($workflowItem, $transition);
        $workflowItem->setUpdated();
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
