<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Builder;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityRelationsBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldSetCustomerDataChannel()
    {
        $channel = new Channel();
        $customer = new B2bCustomer();
        $opportunity = new Opportunity();
        $opportunity->setCustomer($customer);
        $opportunity->setDataChannel($channel);

        $builder = new OpportunityRelationsBuilder($opportunity);
        $builder->build();

        $this->assertSame($channel, $customer->getDataChannel());
    }

    public function testShouldCreateCustomerAccount()
    {
        $customer = new B2bCustomer();
        $customer->setName('John Doe');
        $opportunity = new Opportunity();
        $opportunity->setCustomer($customer);

        $builder = new OpportunityRelationsBuilder($opportunity);
        $builder->build();

        $this->assertNotNull($customer->getAccount());
        $this->assertEquals('John Doe', $customer->getAccount()->getName());
    }

    public function testShouldSetCustomerOrganization()
    {
        $organization = new Organization();
        $customer = new B2bCustomer();
        $opportunity = new Opportunity();
        $opportunity->setCustomer($customer);
        $opportunity->setOrganization($organization);

        $builder = new OpportunityRelationsBuilder($opportunity);
        $builder->build();

        $this->assertSame($organization, $customer->getOrganization());
    }
}
