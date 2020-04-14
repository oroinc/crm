<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Oro\Bundle\SalesBundle\Provider\LeadToOpportunityProvider;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub as Lead;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\OpportunityStub as Opportunity;
use Symfony\Component\PropertyAccess\PropertyAccess;

class LeadToOpportunityProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LeadToOpportunityProvider
     */
    protected $provider;

    protected function setUp(): void
    {
        $entityFieldProvider = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityFieldProvider')
            ->setMethods(['getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        $changeLeadStatus = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Model\ChangeLeadStatus')
            ->setMethods(['qualify'])
            ->disableOriginalConstructor()
            ->getMock();

        $entityFieldProvider->method('getFields')->willReturn([]);

        $this->provider = $this->getMockBuilder('Oro\Bundle\SalesBundle\Provider\LeadToOpportunityProvider')
            ->setConstructorArgs([$entityFieldProvider, $changeLeadStatus])
            ->setMethods(['createOpportunity'])
            ->getMock();
        $this->provider->expects($this->any())
            ->method('createOpportunity')
            ->will($this->returnCallback(function () {
                return new Opportunity();
            }));
    }

    public function testPrepareOpportunityForFormWithContact()
    {
        $lead = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub')
            ->setMethods(['getContact', 'getName', 'getStatus'])
            ->getMock();

        $lead
            ->expects($this->once())
            ->method('getContact')
            ->willReturn(new Contact());

        $lead
            ->expects($this->once())
            ->method('getName')
            ->willReturn('testName');

        $this->provider->prepareOpportunityForForm($lead, true);
    }

    /**
     * @dataProvider leadProvider
     */
    public function testPrepareOpportunityForFormWithoutContact(Lead $lead, Opportunity $expectedOpportunity)
    {
        $preparedOpportunity = $this->provider->prepareOpportunityForForm($lead, true);
        $this->assertEquals($preparedOpportunity, $expectedOpportunity);
    }

    public function leadProvider()
    {
        return [
            'lead_with_address'    => $this->prepareLeadAndOpportunity(),
            'lead_without_address' => $this->prepareLeadAndOpportunity(false),
        ];
    }

    protected function prepareLeadAndOpportunity($withAddress = true)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $user         = $this->createMock('Oro\Bundle\UserBundle\Entity\User');

        $opportunityFields = [
            'firstName'         => 'test_firstName',
            'jobTitle'          => 'test_jobTitle',
            'lastName'          => 'test_lastName',
            'middleName'        => 'test_middleName',
            'namePrefix'        => 'test_namePrefix',
            'nameSuffix'        => 10,
            'owner'             => $user,
        ];

        $addressFields = [
            'firstName'         => 'test_firstName',
            'lastName'          => 'test_lastName',
            'middleName'        => 'test_middleName',
            'namePrefix'        => 'test_namePrefix',
            'nameSuffix'        => 10,
            'city'         => 'test_city',
            'country'      => 'US',
            'label'        => 'test_label',
            'organization' => 'test_organization',
            'postalCode'   => 'test_postalCode',
            'region'       => 'California',
            'regionText'   => 'test_regionText',
            'street'       => 'test_street',
            'street2'      => 'test_street2',
        ];

        $lead = new Lead();
        $lead
            ->setName('test_name')
            ->addPhone(new LeadPhone('test_phone'))
            ->addEmail(new LeadEmail('test_email'))
        ;
        $opportunity = new Opportunity();
        $contact = new Contact();
        $contact->addPhone(new ContactPhone('test_phone'));
        $contact->addEmail(new ContactEmail('test_email'));
        $opportunity
            ->setName('test_name')
            ->setContact($contact)
            ->setLead($lead)
        ;

        foreach ($opportunityFields as $fieldName => $value) {
            $accessor->setValue($lead, $fieldName, $value);
            $accessor->setValue($contact, $fieldName, $value);
        }

        if ($withAddress) {
            $address = new LeadAddress();
            $lead->addAddress($address);
            $address->setPrimary(true);
            $contactAddress = new ContactAddress();
            $contactAddress->setPrimary(true);
            $contact->addAddress($contactAddress);
            foreach ($addressFields as $fieldName => $value) {
                $accessor->setValue($address, $fieldName, $value);
                $accessor->setValue($contactAddress, $fieldName, $value);
            }
        }

        return [ $lead, $opportunity ];
    }
}
