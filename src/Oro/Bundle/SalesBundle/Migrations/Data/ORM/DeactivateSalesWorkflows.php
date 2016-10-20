<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowNotFoundException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

class DeactivateSalesWorkflows extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->container->get('oro_workflow.manager');

        try {
            $workflowManager->deactivateWorkflow('b2b_flow_lead');
        } catch (WorkflowNotFoundException $e) {
        }

        try {
            $workflowManager->deactivateWorkflow('opportunity_flow');
        } catch (WorkflowNotFoundException $e) {
        }
    }
}
