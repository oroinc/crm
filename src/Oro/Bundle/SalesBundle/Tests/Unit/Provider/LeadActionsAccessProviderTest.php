<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\SalesBundle\Provider\LeadActionsAccessProvider;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class LeadActionsAccessProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var FeatureChecker|\PHPUnit\Framework\MockObject\MockObject */
    private $featureChecker;

    /** @var WorkflowRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $workflowRegistry;

    /** @var LeadActionsAccessProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->featureChecker = $this->createMock(FeatureChecker::class);
        $this->workflowRegistry = $this->createMock(WorkflowRegistry::class);

        $this->provider = new LeadActionsAccessProvider($this->workflowRegistry, $this->featureChecker);
    }

    public function testIsDisqualifyAllowedWhenLeadDisqualified(): void
    {
        $this->assertFalse(
            $this->provider->isDisqualifyAllowed($this->getDisqualifiedLead())
        );
    }

    public function testIsDisqualifyAllowedWhenLeadWfEnabled(): void
    {
        $this->makeLeadWfEnabled();
        $this->assertFalse(
            $this->provider->isDisqualifyAllowed($this->getValidLead())
        );
    }

    public function testIsDisqualifyAllowedWhenAllChecksPassed(): void
    {
        $lead = $this->getValidLead();
        $this->makeLeadWfDisabled();
        $this->assertTrue($this->provider->isDisqualifyAllowed($lead));
    }

    public function testIsConvertToOpportunityAllowedWhenLeadHasOpportunities(): void
    {
        $this->assertFalse(
            $this->provider->isConvertToOpportunityAllowed($this->getLeadWithOpportunities())
        );
    }

    public function testIsConvertToOpportunityAllowedWhenLeadWfEnabled(): void
    {
        $this->makeLeadWfEnabled();
        $this->assertFalse(
            $this->provider->isConvertToOpportunityAllowed($this->getValidLead())
        );
    }

    public function testIsConvertToOpportunityAllowedWhenFeatureDisabled(): void
    {
        $lead = $this->getValidLead();
        $this->makeLeadWfDisabled();
        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('sales_opportunity')
            ->willReturn(false);

        $this->assertFalse($this->provider->isConvertToOpportunityAllowed($lead));
    }

    public function testIsConvertToOpportunityAllowedWhenAllChecksPassed(): void
    {
        $lead = $this->getValidLead();
        $this->makeLeadWfDisabled();
        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('sales_opportunity')
            ->willReturn(true);
        $this->assertTrue($this->provider->isConvertToOpportunityAllowed($lead));
    }

    private function getValidLead(): Lead
    {
        $lead = new LeadStub();
        $lead->setStatus(new TestEnumValue(ChangeLeadStatus::STATUS_QUALIFY, 'test'));

        return $lead;
    }

    private function getDisqualifiedLead(): Lead
    {
        $lead = new LeadStub();
        $lead->setStatus(new TestEnumValue(ChangeLeadStatus::STATUS_DISQUALIFY, 'test'));

        return $lead;
    }

    private function getLeadWithOpportunities(): Lead
    {
        $lead = new LeadStub();
        $lead->addOpportunity(new Opportunity());

        return $lead;
    }

    private function makeLeadWfDisabled(): void
    {
        $this->workflowRegistry->expects($this->once())
            ->method('getActiveWorkflowsByEntityClass')
            ->willReturnMap([
                [Lead::class, new ArrayCollection([])]
            ]);
    }

    private function makeLeadWfEnabled(): void
    {
        $this->workflowRegistry->expects($this->once())
            ->method('getActiveWorkflowsByEntityClass')
            ->with(Lead::class)
            ->willReturn(new ArrayCollection([1]));
    }
}
