<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\SalesFunnel;

class SalesFunnelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new SalesFunnel();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function getSetDataProvider()
    {
        $now          = new \DateTime('now');
        $organization = new Organization();
        $lead         = $this->getMockBuilder('Oro\Bundle\SalesBundle\Entity\Lead')
            ->disableOriginalConstructor()
            ->getMock();
        $opportunity  = $this->getMockBuilder('Oro\Bundle\SalesBundle\Entity\Opportunity')
            ->disableOriginalConstructor()
            ->getMock();
        $user         = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $channel      = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');

        return array(
            'startDate'    => array('startDate', $now, $now),
            'lead'         => array('lead', $lead, $lead),
            'opportunity'  => array('opportunity', $opportunity, $opportunity),
            'owner'        => array('owner', $user, $user),
            'createdAt'    => array('createdAt', $now, $now),
            'updatedAt'    => array('updatedAt', $now, $now),
            'organization' => array('organization', $organization, $organization)
        );
    }

    public function testBeforeSave()
    {
        $obj = new SalesFunnel();
        $this->assertNull($obj->getCreatedAt());
        $this->assertNull($obj->getUpdatedAt());
        $obj->beforeSave();

        $this->assertInstanceOf('\DateTime', $obj->getCreatedAt());
        $this->assertNull($obj->getUpdatedAt());
    }

    public function testBeforeUpdate()
    {
        $obj = new SalesFunnel();
        $this->assertNull($obj->getCreatedAt());
        $this->assertNull($obj->getUpdatedAt());
        $obj->beforeUpdate();

        $this->assertInstanceOf('\DateTime', $obj->getUpdatedAt());
        $this->assertNull($obj->getCreatedAt());
    }

    public function testGetLeadEmail()
    {
        $salesFunnel = new SalesFunnel();
        $lead = new Lead();
        $email = new LeadEmail('test@test.com');
        $email->setPrimary(true);
        $lead->addEmail($email);
        $salesFunnel->setLead($lead);

        $this->assertEquals('test@test.com', $salesFunnel->getEmail());
    }

    public function testGetOpportunityEmail()
    {
        $salesFunnel = new SalesFunnel();
        $email = new ContactEmail();
        $email->setEmail('test@test.com');
        $contact = new Contact();
        $contact->addEmail($email);
        $contact->setPrimaryEmail($email);
        $opportunity = new Opportunity();
        $opportunity->setContact($contact);
        $salesFunnel->setOpportunity($opportunity);

        $this->assertEquals('test@test.com', $salesFunnel->getEmail());
    }

    public function testGetFirstName()
    {
        $salesFunnel = new SalesFunnel();
        $opportunity = new Opportunity();
        $opportunity->setName('test');
        $salesFunnel->setOpportunity($opportunity);

        $this->assertEquals('test', $salesFunnel->getFirstName());

        $salesFunnel = new SalesFunnel();
        $lead = new Lead();
        $lead->setName('test2');
        $salesFunnel->setLead($lead);

        $this->assertEquals('test2', $salesFunnel->getFirstName());
    }
}
