<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\SalesFunnel;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

class LeadActionsAccessProvider
{
    /** @var WorkflowRegistry */
    protected $workflowRegistry;

    /** @var FeatureChecker */
    protected $featureChecker;

    /**
     * Used to store cached value of enabled workflows for Lead entity
     *
     * @var bool
     */
    protected $isLeadWfEnabled;

    /**
     * Used to store cached value of enabled workflows for SalesFunnel entity
     *
     * @var bool
     */
    protected $isSalesFunnelWfEnabled;

    public function __construct(WorkflowRegistry $workflowRegistry, FeatureChecker $featureChecker)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->featureChecker   = $featureChecker;
    }

    /**
     * @param Lead $lead
     *
     * @return bool
     */
    public function isDisqualifyAllowed(Lead $lead)
    {
        return $lead->getStatus()->getId() !== ChangeLeadStatus::STATUS_DISQUALIFY &&
               !$this->isLeadWfEnabled() &&
               !$this->isSalesFunnelWfEnabled();
    }

    /**
     * @param Lead $lead
     *
     * @return bool
     */
    public function isConvertToOpportunityAllowed(Lead $lead)
    {
        return $lead->getOpportunities()->count() === 0 &&
               !$this->isLeadWfEnabled() &&
               !$this->isSalesFunnelWfEnabled() &&
               $this->featureChecker->isFeatureEnabled('sales_opportunity');
    }

    /**
     * @return bool
     */
    protected function isLeadWfEnabled()
    {
        if (null === $this->isLeadWfEnabled) {
            $this->isLeadWfEnabled = !$this->workflowRegistry
                ->getActiveWorkflowsByEntityClass(Lead::class)->isEmpty();
        }

        return $this->isLeadWfEnabled;
    }

    /**
     * @return bool
     */
    protected function isSalesFunnelWfEnabled()
    {
        if (null === $this->isSalesFunnelWfEnabled) {
            $this->isSalesFunnelWfEnabled = !$this->workflowRegistry
                ->getActiveWorkflowsByEntityClass(SalesFunnel::class)->isEmpty();
        }

        return $this->isSalesFunnelWfEnabled;
    }
}
