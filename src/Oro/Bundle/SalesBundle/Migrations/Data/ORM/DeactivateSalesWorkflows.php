<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     *
     * @todo remove enabling features once it's possible to use workflow in code if features are disabled
     */
    public function load(ObjectManager $manager)
    {
        $requiredFeatureToggles = [
            'oro_sales.lead_feature_enabled',
            'oro_sales.opportunity_feature_enabled',
        ];

        $originalFeatureTogglesSetting = [];

        $cm = $this->container->get('oro_config.global');
        foreach ($requiredFeatureToggles as $toggle) {
            $originalFeatureTogglesSetting[$toggle] = $cm->get($toggle, false, true);
            $cm->set($toggle, true);
        }

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->container->get('oro_workflow.manager');

        $workflowManager->deactivateWorkflow('b2b_flow_lead');
        $workflowManager->deactivateWorkflow('opportunity_flow');

        foreach ($originalFeatureTogglesSetting as $toggle => $setting) {
            if (!isset($setting['use_parent_scope_value']) || $setting['use_parent_scope_value']) {
                $cm->reset($toggle);
            } else {
                $cm->set($toggle, $setting['value']);
            }
        }
    }
}
