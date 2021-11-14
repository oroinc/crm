<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\SalesFunnel;
use Oro\Bundle\UserBundle\Entity\User;

class SalesFunnelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new SalesFunnel();

        call_user_func([$obj, 'set' . ucfirst($property)], $value);
        $this->assertEquals($expected, call_user_func_array([$obj, 'get' . ucfirst($property)], []));
    }

    public function getSetDataProvider(): array
    {
        $now = new \DateTime('now');
        $organization = new Organization();
        $lead = $this->createMock(Lead::class);
        $opportunity = $this->createMock(Opportunity::class);
        $user = $this->createMock(User::class);

        return [
            'startDate'    => ['startDate', $now, $now],
            'lead'         => ['lead', $lead, $lead],
            'opportunity'  => ['opportunity', $opportunity, $opportunity],
            'owner'        => ['owner', $user, $user],
            'createdAt'    => ['createdAt', $now, $now],
            'updatedAt'    => ['updatedAt', $now, $now],
            'organization' => ['organization', $organization, $organization]
        ];
    }

    public function testBeforeSave()
    {
        $obj = new SalesFunnel();
        $this->assertNull($obj->getCreatedAt());
        $this->assertNull($obj->getUpdatedAt());
        $obj->beforeSave();

        $this->assertInstanceOf(\DateTime::class, $obj->getCreatedAt());
        $this->assertNull($obj->getUpdatedAt());
    }

    public function testBeforeUpdate()
    {
        $obj = new SalesFunnel();
        $this->assertNull($obj->getCreatedAt());
        $this->assertNull($obj->getUpdatedAt());
        $obj->beforeUpdate();

        $this->assertInstanceOf(\DateTime::class, $obj->getUpdatedAt());
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
