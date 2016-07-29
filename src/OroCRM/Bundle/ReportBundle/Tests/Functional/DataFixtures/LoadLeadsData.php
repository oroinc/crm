<?php

namespace OroCRM\Bundle\ReportBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadPhone;
use OroCRM\Bundle\SalesBundle\Entity\LeadEmail;
use OroCRM\Bundle\SalesBundle\Entity\LeadAddress;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Migrations\Data\ORM\DefaultChannelData;

class LoadLeadsData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    const FLUSH_MAX = 50;

    /** @var ContainerInterface */
    protected $container;

    /** @var User[] */
    protected $users;

    /** @var Country[] */
    protected $countries;

    /** @var WorkflowManager */
    protected $workflowManager;

    /** @var  EntityManager */
    protected $em;

    /** @var  ConfigManager */
    protected $configManager;

    /** @var Organization */
    protected $organization;

    /** @var BuilderFactory */
    protected $channelBuilderFactory;

    /** @var Channel */
    protected $channel;

    /** @var AbstractEnumValue[] */
    protected $sources;


    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container             = $container;
        $this->workflowManager       = $container->get('oro_workflow.manager');
        $this->configManager         = $container->get('oro_entity_config.config_manager');
        $this->channelBuilderFactory = $container->get('orocrm_channel.builder.factory');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $this->initSupportingEntities($manager);
        $this->loadLeads($manager);
    }

    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }

        $this->users     = $this->em->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $this->em->getRepository('OroAddressBundle:Country')->findAll();

        $className     = ExtendHelper::buildEnumValueClassName('lead_source');
        $enumRepo      = $manager->getRepository($className);
        $this->sources = $enumRepo->findAll();

        $this->channel = $this
            ->channelBuilderFactory
            ->createBuilder()
            ->setChannelType(DefaultChannelData::B2B_CHANNEL_TYPE)
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setEntities()
            ->getChannel();

        $manager->persist($this->channel);
        $manager->flush($this->channel);
    }

    public function loadLeads(ObjectManager $manager)
    {
        $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . "leads.csv", "r");
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $randomUser = count($this->users) - 1;
            $i          = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $user = $this->users[mt_rand(0, $randomUser)];
                $this->setSecurityContext($user);

                $data = array_combine($headers, array_values($data));

                $lead = $this->createLead($manager, $data, $user);
                $this->em->persist($lead);

                $this->loadSalesFlows($lead);

                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $this->em->flush();
                }
            }

            $this->em->flush();
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
            [
                'opportunity_name' => $lead->getName(),
                'company_name' => $lead->getCompanyName(),
            ]
        );
        if ($this->getRandomBoolean()) {
            /** @var Opportunity $opportunity */
            $opportunity   = $leadWorkflowItem->getResult()->get('opportunity');
            $salesFlowItem = $this->workflowManager->startWorkflow(
                'opportunity_flow',
                $opportunity,
                '__start__',
                [
                    'budget_amount'     => mt_rand(10, 10000),
                    'customer_need'     => mt_rand(10, 10000),
                    'proposed_solution' => mt_rand(10, 10000),
                    'probability'       => round(mt_rand(50, 85) / 100.00, 2)
                ]
            );

            if ($this->getRandomBoolean()) {
                if ($this->getRandomBoolean()) {
                    $this->transit(
                        $this->workflowManager,
                        $salesFlowItem,
                        'close_won',
                        [
                            'close_revenue' => mt_rand(100, 1000),
                            'close_date'    => new \DateTime('now'),
                        ]
                    );
                } else {
                    $this->transit(
                        $this->workflowManager,
                        $salesFlowItem,
                        'close_lost',
                        [
                            'close_reason_name' => 'cancelled',
                            'close_revenue'     => mt_rand(100, 1000),
                            'close_date'        => new \DateTime('now'),
                        ]
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
        return (bool)mt_rand(0, 1);
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $securityContext = $this->container->get('security.context');
        $token           = new UsernamePasswordOrganizationToken($user, $user->getUsername(
        ), 'main', $this->organization);
        $securityContext->setToken($token);
    }

    /**
     * @param ObjectManager $manager
     * @param  array $data
     * @param User $user
     *
     * @return Lead
     */
    protected function createLead(ObjectManager $manager, array $data, $user)
    {
        $lead = new Lead();
        
        $className = ExtendHelper::buildEnumValueClassName(Lead::INTERNAL_STATUS_CODE);
        $defaultStatus = $manager->getRepository($className)->find(ExtendHelper::buildEnumValueId('new'));
        
        $lead->setStatus($defaultStatus);
        $lead->setName($data['Company']);
        $lead->setFirstName($data['GivenName']);
        $lead->setLastName($data['Surname']);

        $leadEmail = new LeadEmail($data['EmailAddress']);
        $leadEmail->setPrimary(true);
        $lead->addEmail($leadEmail);

        $leadPhone = new LeadPhone($data['TelephoneNumber']);
        $leadPhone->setPrimary(true);
        $lead->addPhone($leadPhone);

        $lead->setCompanyName($data['Company']);
        $lead->setOwner($user);
        $lead->setDataChannel($this->channel);
        /** @var LeadAddress $address */
        $address = new LeadAddress();
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

        $lead->addAddress($address);

        $countSources = count($this->sources) - 1;
        $source       = $this->sources[mt_rand(0, $countSources)];
        $lead->setSource($source);

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

    public function getOrder()
    {
        return 300;
    }
}
