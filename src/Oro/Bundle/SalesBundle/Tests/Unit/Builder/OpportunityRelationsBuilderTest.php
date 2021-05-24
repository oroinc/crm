<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Builder;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\OpportunityStub;
use Oro\Component\Testing\ReflectionUtil;

class OpportunityRelationsBuilderTest extends \PHPUnit\Framework\TestCase
{
    /** @var OpportunityRelationsBuilder */
    private $relationsBuilder;

    protected function setUp(): void
    {
        $this->relationsBuilder = new OpportunityRelationsBuilder();
    }

    private function getCustomer(Account $account, object $target = null): Customer
    {
        $customer = new CustomerStub();
        $customer->setTarget($account, $target);

        return $customer;
    }

    private function getOpportunity(Customer $customer): Opportunity
    {
        $opportunity = new OpportunityStub();
        $opportunity->setCustomerAssociation($customer);

        return $opportunity;
    }

    private function getB2bCustomer(int $id = null): B2bCustomer
    {
        $b2bCustomer = new B2bCustomer();
        ReflectionUtil::setId($b2bCustomer, $id);

        return $b2bCustomer;
    }

    public function testShouldSetCustomerOrganization()
    {
        $organization = new Organization();
        $b2bCustomer = $this->getB2bCustomer();
        $customer = $this->getCustomer(new Account(), $b2bCustomer);

        $opportunity = $this->getOpportunity($customer);
        $opportunity->setOrganization($organization);

        $this->relationsBuilder->buildCustomer($opportunity);

        $this->assertSame($organization, $b2bCustomer->getOrganization());
    }

    /**
     * @dataProvider customerRelationIdentifiersProvider
     */
    public function testShouldSetCustomerContactIfAtLeastOneOrBothRecordsAreNew(?int $b2bCustomerId, ?int $contactId)
    {
        $b2bCustomer = $this->getB2bCustomer($b2bCustomerId);
        $customer = $this->getCustomer(new Account(), $b2bCustomer);

        $opportunityContact = new Contact();
        $opportunityContact->setId($contactId);
        $opportunity = $this->getOpportunity($customer);
        $opportunity->setContact($opportunityContact);

        $this->relationsBuilder->buildCustomer($opportunity);

        $this->assertSame($opportunityContact, $b2bCustomer->getContact());
    }

    public function customerRelationIdentifiersProvider(): array
    {
        return [
            ['customerId' => 69, 'contactId' => null],
            ['customerId' => null, 'contactId' => 69],
            ['customerId' => null, 'contactId' => null],
        ];
    }

    public function testShouldNotSetCustomerContactIfAlreadyExists()
    {
        $contact = new Contact();
        $b2bCustomer = $this->getB2bCustomer();
        $b2bCustomer->setContact($contact);
        $customer = $this->getCustomer(new Account(), $b2bCustomer);

        $opportunityContact = new Contact();
        $opportunity = $this->getOpportunity($customer);
        $opportunity->setContact($opportunityContact);

        $this->relationsBuilder->buildCustomer($opportunity);

        $this->assertSame($contact, $b2bCustomer->getContact());
        $this->assertNotSame($opportunityContact, $b2bCustomer->getContact());
    }

    public function testShouldNotSetCustomerContactIfBothRecordsAreOld()
    {
        $b2bCustomer = $this->getB2bCustomer(1);
        $customer = $this->getCustomer(new Account(), $b2bCustomer);

        $opportunityContact = new Contact();
        $opportunityContact->setId(1);
        $opportunity = $this->getOpportunity($customer);
        $opportunity->setContact($opportunityContact);

        $this->relationsBuilder->buildCustomer($opportunity);

        $this->assertNull($b2bCustomer->getContact());
    }

    /**
     * @dataProvider accountRelationIdentifiersProvider
     */
    public function testShouldAddContactToAccountIfAtLeastOneOrBothRecordsAreNew(?int $accountId, ?int $contactId)
    {
        $contact = new Contact();
        $contact->setId($contactId);
        $account = new Account();
        $account->setId($accountId);

        $b2bCustomer = $this->getB2bCustomer();
        $b2bCustomer->setAccount($account);
        $customer = $this->getCustomer(new Account(), $b2bCustomer);

        $opportunity = $this->getOpportunity($customer);
        $opportunity->setContact($contact);

        $this->relationsBuilder->buildAccount($opportunity);

        $this->assertFalse($account->getContacts()->contains($contact));
    }

    public function accountRelationIdentifiersProvider(): array
    {
        return [
            ['accountId' => 69, 'contactId' => null],
            ['accountId' => null, 'contactId' => 69],
            ['accountId' => null, 'contactId' => null],
        ];
    }
}
