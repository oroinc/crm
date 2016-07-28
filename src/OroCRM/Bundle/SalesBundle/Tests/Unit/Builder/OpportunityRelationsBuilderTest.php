<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Builder;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
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
        $builder->buildCustomer();

        $this->assertSame($channel, $customer->getDataChannel());
    }

    public function testShouldCreateCustomerAccount()
    {
        $customer = new B2bCustomer();
        $customer->setName('John Doe');
        $opportunity = new Opportunity();
        $opportunity->setCustomer($customer);

        $builder = new OpportunityRelationsBuilder($opportunity);
        $builder->buildCustomer();

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
        $builder->buildCustomer();

        $this->assertSame($organization, $customer->getOrganization());
    }

    /**
     * @dataProvider relationIdentifiersProvider
     *
     * @param int|null $accountId
     * @param int|null $contactId
     */
    public function testShouldAddContactToAccount($accountId, $contactId)
    {
        $contact = new Contact();
        $contact->setId($contactId);
        $account = new Account();
        $account->setId($accountId);

        $customer = new B2bCustomer();
        $customer->setAccount($account);

        $opportunity = new Opportunity();
        $opportunity->setCustomer($customer);
        $opportunity->setContact($contact);

        $builder = new OpportunityRelationsBuilder($opportunity);
        $builder->buildAccount();

        $this->assertTrue($account->getContacts()->contains($contact));
    }

    public function relationIdentifiersProvider()
    {
        return [
            ['accountId' => 69, 'contactId' => null],
            ['accountId' => null, 'contactId' => 69],
            ['accountId' => null, 'contactId' => null],
        ];
    }
}
