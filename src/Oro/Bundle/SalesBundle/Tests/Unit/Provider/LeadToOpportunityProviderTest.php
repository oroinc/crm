<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;
use Oro\Bundle\SalesBundle\Entity\LeadPhone;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\SalesBundle\Provider\LeadToOpportunityProvider;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub as Lead;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\OpportunityStub as Opportunity;
use Oro\Bundle\UserBundle\Entity\User;

class LeadToOpportunityProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var LeadToOpportunityProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $provider;

    protected function setUp(): void
    {
        $entityFieldProvider = $this->createMock(EntityFieldProvider::class);
        $changeLeadStatus = $this->createMock(ChangeLeadStatus::class);

        $entityFieldProvider->expects(self::any())
            ->method('getEntityFields')
            ->willReturn([]);

        $this->provider = $this->getMockBuilder(LeadToOpportunityProvider::class)
            ->setConstructorArgs([$entityFieldProvider, $changeLeadStatus])
            ->onlyMethods(['createOpportunity'])
            ->getMock();
        $this->provider->expects(self::any())
            ->method('createOpportunity')
            ->willReturnCallback(function () {
                return new Opportunity();
            });
    }

    public function testPrepareOpportunityForFormWithContact(): void
    {
        $lead = $this->createMock(Lead::class);
        $lead->expects(self::once())
            ->method('getContact')
            ->willReturn(new Contact());
        $lead->expects(self::once())
            ->method('getName')
            ->willReturn('testName');

        $this->provider->prepareOpportunityForForm($lead, true);
    }

    /**
     * @dataProvider leadProvider
     */
    public function testPrepareOpportunityForFormWithoutContact(Lead $lead, Opportunity $expectedOpportunity): void
    {
        $preparedOpportunity = $this->provider->prepareOpportunityForForm($lead, true);
        self::assertEquals($preparedOpportunity, $expectedOpportunity);
    }

    public function leadProvider(): array
    {
        return [
            'lead_with_address'    => $this->prepareLeadAndOpportunity(),
            'lead_without_address' => $this->prepareLeadAndOpportunity(false),
        ];
    }

    private function prepareLeadAndOpportunity(bool $withAddress = true): array
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $user = $this->createMock(User::class);

        $opportunityFields = [
            'firstName'  => 'test_firstName',
            'jobTitle'   => 'test_jobTitle',
            'lastName'   => 'test_lastName',
            'middleName' => 'test_middleName',
            'namePrefix' => 'test_namePrefix',
            'nameSuffix' => 10,
            'owner'      => $user,
        ];

        $addressFields = [
            'firstName'    => 'test_firstName',
            'lastName'     => 'test_lastName',
            'middleName'   => 'test_middleName',
            'namePrefix'   => 'test_namePrefix',
            'nameSuffix'   => 10,
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
            ->addEmail(new LeadEmail('test_email'));
        $opportunity = new Opportunity();
        $contact = new Contact();
        $contact->addPhone(new ContactPhone('test_phone'));
        $contact->addEmail(new ContactEmail('test_email'));
        $opportunity
            ->setName('test_name')
            ->setContact($contact)
            ->setLead($lead);

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

        return [$lead, $opportunity];
    }
}
