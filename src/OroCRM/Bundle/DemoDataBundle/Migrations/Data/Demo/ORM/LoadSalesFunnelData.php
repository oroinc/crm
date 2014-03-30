<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\SalesFunnel;

class LoadSalesFunnelData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    const FLUSH_MAX = 50;

    protected $probabilities = array (0.2, 0.5, 0.8);
    /**
     * @var ContainerInterface
     */
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
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadLeadsData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadOpportunitiesData'
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
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadFlows();
    }

    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }

        $this->users = $this->em->getRepository('OroUserBundle:User')->findAll();
        $this->leads = $this->em->getRepository('OroCRMSalesBundle:Lead')->findAll();
        $this->opportunities = $this->em->getRepository('OroCRMSalesBundle:Opportunity')->findAll();
    }

    protected function loadFlows()
    {
        $randomUser = count($this->users) - 1;

        $leads = array_rand($this->leads, 25);
        $opportunities = array_rand($this->opportunities, 25);

        foreach ($leads as $lead) {
            $user = $this->users[mt_rand(0, $randomUser)];
            $this->setSecurityContext($user);
            $this->loadSalesFlows($this->leads[$lead], $user);
        }
        $this->flush($this->em);

        foreach ($opportunities as $opportunity) {
            $user = $this->users[mt_rand(0, $randomUser)];
            $this->setSecurityContext($user);
            $this->loadSalesFlows($this->opportunities[$opportunity], $user);
        }
        $this->flush($this->em);
    }

    /**
     * @param Lead | Opportunity $entity
     * @param User $owner
     */
    protected function loadSalesFlows($entity, $owner)
    {
        if ($entity instanceof Lead) {
            $step = 'start_from_lead';
            $parameters = array('lead' => $entity);
        } else {
            $step = 'start_from_opportunity';
            $parameters = array('opportunity' => $entity);
        }

        $parameters = array_merge(array(
            'sales_funnel' => null,
            'sales_funnel_owner' => $owner,
            'sales_funnel_start_date' => new \DateTime('now'),
        ), $parameters);

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

        if ($entity instanceof Lead) {
            if ($this->isTransitionAllowed($salesFunnelItem, 'qualify')) {
                $this->workflowManager->transit($salesFunnelItem, 'qualify');
            } else {
                return;
            }
        }

        if (rand(1, 100) > 10) {
            $salesFunnelItem->getData()
                ->set('budget_amount', mt_rand(10, 10000))
                ->set('customer_need', mt_rand(10, 10000))
                ->set('proposed_solution', mt_rand(10, 10000))
                ->set('probability', $this->probabilities[array_rand($this->probabilities)]);

            if ($this->isTransitionAllowed($salesFunnelItem, 'develop')) {
                $this->workflowManager->transit($salesFunnelItem, 'develop');
                if ($this->getRandomBoolean()) {
                    $salesFunnelItem->getData()
                        ->set('close_revenue', mt_rand(10, 1000))
                        ->set('close_date', new \DateTime());

                    if ($this->getRandomBoolean()) {
                        if ($this->isTransitionAllowed($salesFunnelItem, 'close_as_won')) {
                            $this->workflowManager->transit($salesFunnelItem, 'close_as_won');
                        }
                    } else {
                        $salesFunnelItem->getData()
                            ->set('close_reason_name', 'cancelled')
                            ->set('close_date', new \DateTime('now', new \DateTimeZone('UTC')));
                        if ($this->isTransitionAllowed($salesFunnelItem, 'close_as_lost')) {
                            $this->workflowManager->transit($salesFunnelItem, 'close_as_lost');
                        }
                    }
                }
            }
        }
    }

    /**
     * @param WorkflowItem $workflowItem
     * @param string $transition
     * @return bool
     */
    protected function isTransitionAllowed(WorkflowItem $workflowItem, $transition)
    {
        $workflow = $this->workflowManager->getWorkflow($workflowItem);

        return $workflow->isTransitionAllowed($workflowItem, $transition);
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
     * Flush objects
     *
     * @param mixed $manager
     */
    private function flush($manager)
    {
        $manager->flush();
    }
}
