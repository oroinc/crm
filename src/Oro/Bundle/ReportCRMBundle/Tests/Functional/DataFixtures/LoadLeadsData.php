<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Migrations\Data\ORM\DefaultChannelData;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadLeadsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    private const FLUSH_MAX = 50;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $workflowManager = $this->container->get('oro_workflow.manager');
        $tokenStorage = $this->container->get('security.token_storage');
        $users = $manager->getRepository(User::class)->findAll();
        $countries = $manager->getRepository(Country::class)->findAll();
        $sources = $manager->getRepository(EnumOption::class)
            ->findBy(['enumCode' => Lead::INTERNAL_STATUS_CODE]);
        $this->createChannel($manager);

        $handle = fopen(__DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . 'leads.csv', 'r');
        if ($handle) {
            $headers = [];
            if (($data = fgetcsv($handle, 1000, ',')) !== false) {
                //read headers
                $headers = $data;
            }
            $randomUser = \count($users) - 1;
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $user = $users[mt_rand(0, $randomUser)];
                $tokenStorage->setToken(new UsernamePasswordOrganizationToken(
                    $user,
                    'main',
                    $this->getReference(LoadOrganization::ORGANIZATION),
                    $user->getUserRoles()
                ));

                $data = array_combine($headers, array_values($data));

                $lead = $this->createLead($manager, $data, $user, $countries, $sources);
                $manager->persist($lead);

                $this->loadSalesFlows($workflowManager, $lead);

                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $manager->flush();
                }
            }

            $manager->flush();
            fclose($handle);

            $tokenStorage->setToken(null);
        }
    }

    private function createChannel(ObjectManager $manager): Channel
    {
        $channel = $this->container->get('oro_channel.builder.factory')
            ->createBuilder()
            ->setChannelType(DefaultChannelData::B2B_CHANNEL_TYPE)
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setEntities()
            ->getChannel();
        $manager->persist($channel);
        $manager->flush($channel);

        return $channel;
    }

    private function loadSalesFlows(WorkflowManager $workflowManager, Lead $lead): void
    {
        $leadWorkflowItem = $workflowManager->startWorkflow(
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
            $salesFlowItem = $workflowManager->startWorkflow(
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
                        $workflowManager,
                        $salesFlowItem,
                        'close_won',
                        [
                            'close_revenue' => $closeRevenue,
                            'close_date'    => new \DateTime('now'),
                        ]
                    );
                } else {
                    $this->transit(
                        $workflowManager,
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

    private function getRandomBoolean(): bool
    {
        return (bool)mt_rand(0, 1);
    }

    private function createLead(
        ObjectManager $manager,
        array $data,
        User $user,
        array $countries,
        array $sources
    ): Lead {
        $lead = new Lead();

        $defaultStatus = $manager->getRepository(EnumOption::class)
            ->find(ExtendHelper::buildEnumOptionId(
                Lead::INTERNAL_STATUS_CODE,
                ExtendHelper::buildEnumInternalId('new')
            ));

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

        $address = new LeadAddress();
        $address->setLabel('Primary Address');
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);

        $isoCode = $data['Country'];
        $country = array_filter(
            $countries,
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

        $lead->setSource($sources[mt_rand(0, \count($sources) - 1)]);

        return $lead;
    }

    private function transit(
        WorkflowManager $workflowManager,
        WorkflowItem $workflowItem,
        string $transition,
        array $data
    ): void {
        foreach ($data as $key => $value) {
            $workflowItem->getData()->set($key, $value);
        }

        $workflow = $workflowManager->getWorkflow($workflowItem);
        $workflow->transit($workflowItem, $transition);
        $workflowItem->setUpdated();
    }
}
