<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Migrations\Data\ORM\DefaultChannelData;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    /** @var EntityManager */
    protected $em;

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
        $this->channelBuilderFactory = $container->get('oro_channel.builder.factory');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository(Organization::class)->getFirst();
        $this->initSupportingEntities($manager);
        $this->loadLeads($manager);
    }

    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }

        $this->users = $this->em->getRepository(User::class)->findAll();
        $this->countries = $this->em->getRepository(Country::class)->findAll();

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
        $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . 'leads.csv', 'r');
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ',')) !== false) {
                //read headers
                $headers = $data;
            }
            $randomUser = count($this->users) - 1;
            $i          = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
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
        // change test according to CRM-6344
        if ($this->getRandomBoolean()) {
            /** @var Opportunity $opportunity */
            $opportunity   = $leadWorkflowItem->getResult()->get('opportunity');
            $budgetAmount = MultiCurrency::create(mt_rand(10, 10000), 'USD');
            $closeRevenue = MultiCurrency::create(mt_rand(10, 10000), 'USD');
            $salesFlowItem = $this->workflowManager->startWorkflow(
                'opportunity_flow',
                $opportunity,
                '__start__',
                [
                    'budget_amount'     => $budgetAmount,
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
                            'close_revenue' => $closeRevenue,
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
                            'close_revenue'     => $closeRevenue,
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
        $tokenStorage = $this->container->get('security.token_storage');
        $token = new UsernamePasswordOrganizationToken(
            $user,
            $user->getUsername(),
            'main',
            $this->organization,
            $user->getUserRoles()
        );
        $tokenStorage->setToken($token);
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

        $workflow->transit($workflowItem, $transition);
        $workflowItem->setUpdated();
    }

    public function getOrder()
    {
        return 300;
    }
}
