<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\SalesFunnel;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Model\Filter\WorkflowDefinitionFilters;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads sales funnel data
 */
class LoadSalesFunnelData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var array */
    protected $probabilities = [0.2, 0.5, 0.8];

    /** @var ContainerInterface */
    protected $container;

    /** @var  User[] */
    protected $users;

    /** @var  Lead[] */
    protected $leads;

    /** @var  Opportunity[] */
    protected $opportunities;

    /** @var WorkflowManager */
    protected $workflowManager;

    /** @var  EntityManager */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadLeadsData::class,
            LoadOpportunitiesData::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->workflowManager = $container->get('oro_workflow.manager');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadFlows();

        $tokenStorage = $this->container->get('security.token_storage');
        $tokenStorage->setToken(null);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }

        $this->users = $this->em->getRepository('OroUserBundle:User')->findAll();
        $this->leads = $this->getRandomEntityRecords('OroSalesBundle:Lead');
        $this->opportunities = $this->getRandomEntityRecords('OroSalesBundle:Opportunity');
    }

    /**
     * @param string $entityName
     * @param int $limit
     *
     * @return array
     */
    protected function getRandomEntityRecords($entityName, $limit = 25)
    {
        $repo = $this->em->getRepository($entityName);

        $entityIds = $repo->createQueryBuilder('e')
            ->select('e.id')
            ->getQuery()
            ->getScalarResult();

        if (count($entityIds) > $limit) {
            $rawList = [];
            foreach ($entityIds as $key => $value) {
                // due array_rand() will pick only keywords
                $rawList[$value['id']] = null;
            }

            $keyList = array_rand($rawList, $limit);

            $criteria = new Criteria();
            $criteria->where(Criteria::expr()->in('id', $keyList));

            $result = $repo->createQueryBuilder('e')
                ->addCriteria($criteria)
                ->getQuery()
                ->getResult();
        } else {
            $result = $repo->findAll();
        }

        return $result;
    }

    protected function loadFlows()
    {
        /* @var $filters WorkflowDefinitionFilters */
        $filters = $this->container->get('oro_workflow.registry.definition_filters');
        $filters->setEnabled(false); // disable filters, because some workflows disabled by `features` by default

        $randomUser = count($this->users) - 1;

        foreach ($this->leads as $lead) {
            $user = $this->users[mt_rand(0, $randomUser)];
            $this->setSecurityContext($user);
            $this->loadSalesFlows($lead, $user);
        }
        $this->flush($this->em);

        foreach ($this->opportunities as $opportunity) {
            $user = $this->users[mt_rand(0, $randomUser)];
            $this->setSecurityContext($user);
            $this->loadSalesFlows($opportunity, $user);
        }
        $this->flush($this->em);

        $filters->setEnabled(true);
    }

    /**
     * @param Lead | Opportunity $entity
     * @param User $owner
     */
    protected function loadSalesFlows($entity, $owner)
    {
        if ($entity instanceof Lead) {
            $step = 'start_from_lead';
            $parameters = ['lead' => $entity];
        } else {
            $step = 'start_from_opportunity';
            $parameters = ['opportunity' => $entity];
        }

        $parameters = array_merge(
            [
                'sales_funnel' => null,
                'sales_funnel_owner' => $owner,
                'sales_funnel_start_date' => new \DateTime('now'),
            ],
            $parameters
        );

        $salesFunnel = new SalesFunnel();
        if (!$this->workflowManager->isStartTransitionAvailable(
            'b2b_flow_sales_funnel',
            $step,
            $salesFunnel,
            $parameters
        )) {
            return;
        }

        $salesFunnelItem = $this->workflowManager->startWorkflow(
            'b2b_flow_sales_funnel',
            $salesFunnel,
            $step,
            $parameters
        );

        $salesFunnelItem->getData()
            ->set('new_opportunity_name', $entity->getName())
            ->set('new_company_name', $entity->getName());

        if ($entity instanceof Lead && !$this->workflowManager->transitIfAllowed($salesFunnelItem, 'qualify')) {
            return;
        }

        if (rand(1, 100) > 10) {
            $budgetAMountVal = mt_rand(10, 10000);
            $salesFunnelItem->getData()
                ->set('budget_amount', MultiCurrency::create($budgetAMountVal, 'USD'))
                ->set('customer_need', mt_rand(10, 10000))
                ->set('proposed_solution', mt_rand(10, 10000))
                ->set('probability', $this->probabilities[array_rand($this->probabilities)]);

            if ($this->workflowManager->transitIfAllowed($salesFunnelItem, 'develop') && $this->getRandomBoolean()) {
                $closeRevenueVal = mt_rand(10, 1000);
                $salesFunnelItem->getData()
                    ->set('close_revenue', MultiCurrency::create($closeRevenueVal, 'USD'))
                    ->set('close_date', new \DateTime());

                if ($this->getRandomBoolean()) {
                    $this->workflowManager->transitIfAllowed($salesFunnelItem, 'close_as_won');
                } else {
                    $salesFunnelItem->getData()
                        ->set('close_reason_name', 'cancelled')
                        ->set('close_date', new \DateTime('now', new \DateTimeZone('UTC')));
                    $this->workflowManager->transitIfAllowed($salesFunnelItem, 'close_as_lost');
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
        $tokenStorage = $this->container->get('security.token_storage');
        $token = new UsernamePasswordOrganizationToken(
            $user,
            $user->getUsername(),
            'main',
            $user->getOrganization()
        );
        $tokenStorage->setToken($token);
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
}
