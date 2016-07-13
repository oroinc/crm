<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\AddressBundle\Entity\Address;

use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\SalesBundle\Model\B2bGuesser;
use OroCRM\Bundle\SalesBundle\Provider\LeadToOpportunityProvider;
use OroCRM\Bundle\SalesBundle\Model\ChangeLeadStatus;

use Symfony\Component\PropertyAccess\PropertyAccess;

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
        $entityFieldProvider = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityFieldProvider')
            ->setMethods(['getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        $changeLeadStatus = $this
            ->getMockBuilder('OroCRM\Bundle\SalesBundle\Model\ChangeLeadStatus')
            ->setMethods(['qualify'])
            ->disableOriginalConstructor()
            ->getMock();

        $entityFieldProvider->method('getFields')->willReturn([]);
        $this->provider = new LeadToOpportunityProvider($b2bGuesser, $entityFieldProvider, $changeLeadStatus);
    }

    public function testPrepareOpportunityForFormWithContact()
    {
        $lead = $this
            ->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\Lead')
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
        $user         = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

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

        $opportunityToAddressFields = [
            'firstName',
            'lastName',
            'middleName',
            'namePrefix',
            'nameSuffix'
        ];

        $lead = new Lead();
        $lead
            ->setName('test_name')
            ->setPhoneNumber('test_phone')
            ->setEmail('test_email')
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
            $address = new Address();
            $lead->setAddress($address);
            $contactAddress = new ContactAddress();
            $contactAddress->setPrimary(true);
            $contact->addAddress($contactAddress);
            foreach ($addressFields as $fieldName => $value) {
                $accessor->setValue($address, $fieldName, $value);
                $accessor->setValue($contactAddress, $fieldName, $value);
            }
            foreach ($opportunityToAddressFields as $fieldName) {
                $accessor->setValue($contactAddress, $fieldName, $opportunityFields[$fieldName]);
            }
        }

        return [ $lead, $opportunity ];
    }
}
