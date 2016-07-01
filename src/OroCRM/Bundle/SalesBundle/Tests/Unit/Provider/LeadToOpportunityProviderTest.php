<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use OroCRM\Bundle\SalesBundle\Provider\LeadToOpportunityProvider;

class LeadToOpportunityProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LeadToOpportunityProvider
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new LeadToOpportunityProvider();
    }

    public function testValidateLeadStatus()
    {


    }

    /**
     * @param string $status
     * @return PHPUnit_Framework_MockObject_MockObject|Lead
     */
    protected function getLeadWithStatus($status)
    {
        $lead = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\Lead')
                     ->setMethods(['getStatus', 'getContact'])
                     ->disableOriginalConstruct()
                     ->getMock();

        $leadStatus = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\Lead')
                           ->setMethods(['getStatus'])
                           ->disableOriginalConstruct()
                           ->getMock();

        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
                    ->setMethods(['getStatus'])
                    ->disableOriginalConstruct()
                    ->getMock();

        $leadStatus->exactly($this->once())
                   ->method('getName')
                   ->will($this->returnValue($status));

        $lead->exactly($this->once())
             ->method('getStatus')
             ->will($this->returnValue($leadStatus));

        $lead->exactly($this->once())
            ->method('getContact')
            ->will($this->returnValue($contact));

        return $lead;
    }
}
