<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\SalesBundle\Tests\Selenium\Pages\SalesFunnel;
use Oro\Component\Testing\Unit\Entity\Stub\StubEnumValue;

use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\SalesBundle\Provider\LeadActionsAccessProvider;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;

class LeadActionsAccessProviderTest  extends \PHPUnit_Framework_TestCase
{
    /** @var LeadActionsAccessProvider */
    protected $provider;

    /** @var FeatureChecker|\PHPUnit_Framework_MockObject_MockObject */
    protected $featureChecker;

    /** @var WorkflowRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $wfRegistry;

    public function setUp()
    {
        $this->featureChecker = $this
            ->getMockBuilder('Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker')
            ->disableOriginalConstructor()
            ->getMock();
        $this->wfRegistry = $this
            ->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new LeadActionsAccessProvider($this->wfRegistry, $this->featureChecker);
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
        $this->featureChecker
            ->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('sales_opportunity')
            ->willReturn(false);

        $this->assertFalse($this->provider->isConvertToOpportunityAllowed($lead));
    }

    public function testIsConvertToOpportunityAllowedWhenAllChecksPassed()
    {
        $lead = $this->getValidLead();
        $this->makeWorkFlowsDisabled();
        $this->featureChecker
            ->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('sales_opportunity')
            ->willReturn(true);
        $this->assertTrue($this->provider->isConvertToOpportunityAllowed($lead));
    }

    /**
     * @return Lead|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getValidLead()
    {
        $lead = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Entity\Lead')
            ->setMethods(['getStatus', 'getOpportunities'])
            ->getMock();
        $lead
            ->expects($this->any())
            ->method('getStatus')
            ->willReturn(new StubEnumValue(ChangeLeadStatus::STATUS_QUALIFY, 'test'));
        $lead
            ->expects($this->any())
            ->method('getOpportunities')
            ->willReturn(new ArrayCollection([]));

        return $lead;
    }

    protected function makeWorkFlowsDisabled()
    {
        $this->wfRegistry
            ->expects($this->at(0))
            ->method('getActiveWorkflowsByEntityClass')
            ->with(Lead::class)
            ->willReturn(new ArrayCollection([]));
        $this->wfRegistry
            ->expects($this->at(1))
            ->method('getActiveWorkflowsByEntityClass')
            ->with(SalesFunnel::class)
            ->willReturn(new ArrayCollection([]));
    }

    /**
     * @return Lead|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDisqualifiedLead()
    {
        $lead = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Entity\Lead')
            ->setMethods(['getStatus'])
            ->getMock();
        $lead
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(new StubEnumValue(ChangeLeadStatus::STATUS_DISQUALIFY, 'test'));

        return $lead;
    }

    /**
     * @return Lead|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLeadWithOpportunities()
    {
        $lead = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Entity\Lead')
            ->setMethods(['getOpportunities'])
            ->getMock();
        $lead
            ->expects($this->once())
            ->method('getOpportunities')
            ->willReturn(new ArrayCollection([1]));

        return $lead;
    }

    public function makeLeadWfEnabled()
    {
        $this->wfRegistry
            ->expects($this->once())
            ->method('getActiveWorkflowsByEntityClass')
            ->with(Lead::class)
            ->willReturn(new ArrayCollection([1]));
    }

    public function makeLeadWfDisabledAndSalesFunnelWfEnabled()
    {
        $this->wfRegistry
            ->expects($this->at(0))
            ->method('getActiveWorkflowsByEntityClass')
            ->with(Lead::class)
            ->willReturn(new ArrayCollection([]));
        $this->wfRegistry
            ->expects($this->at(1))
            ->method('getActiveWorkflowsByEntityClass')
            ->with(SalesFunnel::class)
            ->willReturn(new ArrayCollection([1]));
    }
}
