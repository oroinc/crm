<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use OroCRM\Bundle\SalesBundle\Model\B2bGuesser;
use OroCRM\Bundle\SalesBundle\Provider\LeadToOpportunityProvider;

class LeadToOpportunityProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LeadToOpportunityProvider
     */
    protected $provider;

    public function setUp()
    {
        $b2bGuesser = $this
            ->getMockBuilder('OroCRM\Bundle\SalesBundle\Model\B2bGuesser')
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider = new LeadToOpportunityProvider($b2bGuesser);
    }
    
    public function testInvalidLeadStatus()
    {
        
    }
    
    public function testGetFormId()
    {

    }

    public function testPrepareOpportunity()
    {

    }
}
