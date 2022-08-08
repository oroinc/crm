<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

/**
 * Checks if action with Lead is allowed.
 */
class LeadActionsAccessProvider
{
    private WorkflowRegistry $workflowRegistry;
    private FeatureChecker $featureChecker;

    /**
     * Used to store cached value of enabled workflows for Lead entity
     */
    private ?bool $isLeadWfEnabled = null;

    public function __construct(WorkflowRegistry $workflowRegistry, FeatureChecker $featureChecker)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->featureChecker   = $featureChecker;
    }

    public function isDisqualifyAllowed(Lead $lead): bool
    {
        return $lead->getStatus()->getId() !== ChangeLeadStatus::STATUS_DISQUALIFY &&
               !$this->isLeadWfEnabled();
    }

    public function isConvertToOpportunityAllowed(Lead $lead): bool
    {
        return $lead->getOpportunities()->count() === 0 &&
               !$this->isLeadWfEnabled() &&
               $this->featureChecker->isFeatureEnabled('sales_opportunity');
    }

    private function isLeadWfEnabled(): bool
    {
        if (null === $this->isLeadWfEnabled) {
            $this->isLeadWfEnabled = !$this->workflowRegistry
                ->getActiveWorkflowsByEntityClass(Lead::class)
                ->isEmpty();
        }

        return $this->isLeadWfEnabled;
    }
}
