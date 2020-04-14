<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Builder;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Builder\OpportunityRelationsBuilder;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\OpportunityStub as Opportunity;

class OpportunityRelationsBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OpportunityRelationsBuilder
     */
    protected $relationsBuilder;

    protected function setUp(): void
    {
        $this->markTestSkipped('Due to CRM-7290');
        $this->relationsBuilder = new OpportunityRelationsBuilder();
    }

    public function testShouldSetCustomerOrganization()
    {
        $organization = new Organization();
        $customer = new B2bCustomer();
        $accountCustomer = $this->createAccountCustomer(new Account(), $customer);
        $opportunity = new Opportunity();
        $opportunity->setCustomerAssociation($accountCustomer);
        $opportunity->setOrganization($organization);

        $this->relationsBuilder->buildCustomer($opportunity);

        $this->assertSame($organization, $customer->getOrganization());
    }

    /**
     * @dataProvider customerRelationIdentifiersProvider
     *
     * @param int|null $customerId
     * @param int|null $contactId
     */
    public function testShouldSetCustomerContactIfAtLeastOneOrBothRecordsAreNew($customerId, $contactId)
    {
        $opportunityContact = new Contact();
        $opportunityContact->setId($contactId);
        $customer = new B2bCustomer();
        $this->setObjectId($customer, $customerId);
        $accountCustomer = $this->createAccountCustomer(new Account(), $customer);
        $opportunity = new Opportunity();
        $opportunity->setCustomerAssociation($accountCustomer);
        $opportunity->setContact($opportunityContact);

        $this->relationsBuilder->buildCustomer($opportunity);

        $this->assertSame($opportunityContact, $customer->getContact());
    }

    public function customerRelationIdentifiersProvider()
    {
        return [
            ['customerId' => 69, 'contactId' => null],
            ['customerId' => null, 'contactId' => 69],
            ['customerId' => null, 'contactId' => null],
        ];
    }

    public function testShouldNotSetCustomerContactIfAlreadyExists()
    {
        $customerContact = new Contact();
        $opportunityContact = new Contact();
        $customer = new B2bCustomer();
        $customer->setContact($customerContact);
        $accountCustomer = $this->createAccountCustomer(new Account(), $customer);
        $opportunity = new Opportunity();
        $opportunity->setCustomerAssociation($accountCustomer);
        $opportunity->setContact($opportunityContact);

        $this->relationsBuilder->buildCustomer($opportunity);

        $this->assertSame($customerContact, $customer->getContact());
        $this->assertNotSame($opportunityContact, $customer->getContact());
    }

    public function testShouldNotSetCustomerContactIfBothRecordsAreOld()
    {
        $opportunityContact = new Contact();
        $opportunityContact->setId(1);
        $customer = new B2bCustomer();
        $this->setObjectId($customer, 1);
        $accountCustomer = $this->createAccountCustomer(new Account(), $customer);
        $opportunity = new Opportunity();
        $opportunity->setCustomerAssociation($accountCustomer);
        $opportunity->setContact($opportunityContact);

        $this->relationsBuilder->buildCustomer($opportunity);

        $this->assertNull($customer->getContact());
    }

    /**
     * @dataProvider accountRelationIdentifiersProvider
     *
     * @param int|null $accountId
     * @param int|null $contactId
     */
    public function testShouldAddContactToAccountIfAtLeastOneOrBothRecordsAreNew($accountId, $contactId)
    {
        $contact = new Contact();
        $contact->setId($contactId);
        $account = new Account();
        $account->setId($accountId);

        $customer = new B2bCustomer();
        $customer->setAccount($account);
        $accountCustomer = $this->createAccountCustomer(new Account(), $customer);
        $opportunity = new Opportunity();
        $opportunity->setCustomerAssociation($accountCustomer);
        $opportunity->setContact($contact);

        $this->relationsBuilder->buildAccount($opportunity);

        $this->assertTrue($account->getContacts()->contains($contact));
    }

    public function accountRelationIdentifiersProvider()
    {
        return [
            ['accountId' => 69, 'contactId' => null],
            ['accountId' => null, 'contactId' => 69],
            ['accountId' => null, 'contactId' => null],
        ];
    }

    /**
     * @param object $object
     * @param int $id
     */
    private function setObjectId($object, $id)
    {
        $reflection = new \ReflectionObject($object);
        $propertyReflection = $reflection->getProperty('id');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($object, $id);
    }

    /**
     * @param Account     $account
     * @param object|null $target
     *
     * @return CustomerStub
     */
    private function createAccountCustomer(Account $account, $target = null)
    {
        $customer = new CustomerStub();

        return $customer->setTarget($account, $target);
    }
}
