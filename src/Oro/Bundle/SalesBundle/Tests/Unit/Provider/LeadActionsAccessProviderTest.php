<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\SalesFunnel;
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

    public function testIsDisqualifyAllowedWhenLeadDisqualified()
    {
        $this->assertFalse(
            $this->provider->isDisqualifyAllowed($this->getDisqualifiedLead())
        );
    }

    public function testIsDisqualifyAllowedWhenLeadWfEnabled()
    {
        $this->makeLeadWfEnabled();
        $this->assertFalse(
            $this->provider->isDisqualifyAllowed($this->getValidLead())
        );
    }

    public function testIsDisqualifyAllowedWhenSalesFunnelWfEnabled()
    {
        $lead = $this->getValidLead();
        $this->makeLeadWfDisabledAndSalesFunnelWfEnabled();
        $this->assertFalse($this->provider->isDisqualifyAllowed($lead));
    }

    public function testIsDisqualifyAllowedWhenAllChecksPassed()
    {
        $lead = $this->getValidLead();
        $this->makeWorkFlowsDisabled();
        $this->assertTrue($this->provider->isDisqualifyAllowed($lead));
    }

    public function testIsConvertToOpportunityAllowedWhenLeadHasOpportunities()
    {
        $this->assertFalse(
            $this->provider->isConvertToOpportunityAllowed($this->getLeadWithOpportunities())
        );
    }

    public function testIsConvertToOpportunityAllowedWhenLeadWfEnabled()
    {
        $this->makeLeadWfEnabled();
        $this->assertFalse(
            $this->provider->isConvertToOpportunityAllowed($this->getValidLead())
        );
    }

    public function testIsConvertToOpportunityAllowedWhenSalesFunnelWfEnabled()
    {
        $lead = $this->getValidLead();
        $this->makeLeadWfDisabledAndSalesFunnelWfEnabled();
        $this->assertFalse($this->provider->isConvertToOpportunityAllowed($lead));
    }

    public function testIsConvertToOpportunityAllowedWhenFeatureDisabled()
    {
        $lead = $this->getValidLead();
        $this->makeWorkFlowsDisabled();
        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('sales_opportunity')
            ->willReturn(false);

        $this->assertFalse($this->provider->isConvertToOpportunityAllowed($lead));
    }

    public function testIsConvertToOpportunityAllowedWhenAllChecksPassed()
    {
        $lead = $this->getValidLead();
        $this->makeWorkFlowsDisabled();
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

    private function makeWorkFlowsDisabled()
    {
        $this->workflowRegistry->expects($this->exactly(2))
            ->method('getActiveWorkflowsByEntityClass')
            ->willReturnMap([
                [Lead::class, new ArrayCollection([])],
                [SalesFunnel::class, new ArrayCollection([])]
            ]);
    }

    public function makeLeadWfEnabled()
    {
        $this->workflowRegistry->expects($this->once())
            ->method('getActiveWorkflowsByEntityClass')
            ->with(Lead::class)
            ->willReturn(new ArrayCollection([1]));
    }

    public function makeLeadWfDisabledAndSalesFunnelWfEnabled()
    {
        $this->workflowRegistry->expects($this->exactly(2))
            ->method('getActiveWorkflowsByEntityClass')
            ->willReturnMap([
                [Lead::class, new ArrayCollection([])],
                [SalesFunnel::class, new ArrayCollection([1])]
            ]);
    }
}
