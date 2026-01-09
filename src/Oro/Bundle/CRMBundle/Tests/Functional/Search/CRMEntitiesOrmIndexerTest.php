<?php

declare(strict_types=1);

namespace Oro\Bundle\CRMBundle\Tests\Functional\Search;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SearchBundle\Tests\Functional\Engine\AbstractEntitiesOrmIndexerTest;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Tests that CRM entities can be indexed without type casting errors with the ORM search engine.
 *
 * @group search
 * @dbIsolationPerTest
 */
class CRMEntitiesOrmIndexerTest extends AbstractEntitiesOrmIndexerTest
{
    #[\Override]
    protected function getSearchableEntityClassesToTest(): array
    {
        return [
            Account::class,
            B2bCustomer::class,
            CaseEntity::class,
            CaseMailboxProcessSettings::class,
            Contact::class,
            ContactRequest::class,
            Lead::class,
            Opportunity::class,
        ];
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadOrganization::class, LoadUser::class]);

        $manager = $this->getDoctrine()->getManagerForClass(Account::class);
        /** @var Organization $organization */
        $organization = $this->getReference(LoadOrganization::ORGANIZATION);
        /** @var User $owner */
        $owner = $this->getReference(LoadUser::USER);

        // Create a data channel for Lead, Opportunity, and B2bCustomer
        $channel = (new Channel())
            ->setName('Test Channel')
            ->setChannelType('b2b')
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setCustomerIdentity(B2bCustomer::class)
            ->setOwner($organization);
        $manager->persist($channel);

        $contact = (new Contact())
            ->setFirstName('John')
            ->setMiddleName('M')
            ->setLastName('Doe')
            ->setNamePrefix('Mr.')
            ->setNameSuffix('Jr.')
            ->setDescription('Test contact description')
            ->setJobTitle('Software Engineer')
            ->setFax('555-1234')
            ->setSkype('john.doe.skype')
            ->setTwitter('@johndoe')
            ->setFacebook('facebook.com/johndoe')
            ->setLinkedIn('linkedin.com/in/johndoe')
            ->setGooglePlus('plus.google.com/johndoe')
            ->setOrganization($organization)
            ->setOwner($owner);

        $primaryEmail = (new ContactEmail())->setEmail('john.doe@example.com')->setPrimary(true);
        $contact->addEmail($primaryEmail);

        $primaryPhone = (new ContactPhone())->setPhone('555-0100')->setPrimary(true);
        $contact->addPhone($primaryPhone);

        $this->persistTestEntity($contact);

        $account = (new Account())
            ->setName('Test Account')
            ->setDefaultContact($contact)
            ->setOrganization($organization)
            ->setOwner($owner);
        $this->persistTestEntity($account);

        $contactReason = new ContactReason('General Inquiry');
        $manager->persist($contactReason);

        $contactRequest = new ContactRequest();
        $contactRequest->setFirstName('Jane');
        $contactRequest->setLastName('Smith');
        $contactRequest->setEmailAddress('jane.smith@example.com');
        $contactRequest->setComment('Test comment');
        $contactRequest->setContactReason($contactReason);
        $contactRequest->setCustomerName('Test Customer');
        $contactRequest->setPhone('555-0200');
        $contactRequest->setOwner($organization);
        $this->persistTestEntity($contactRequest);

        $caseEntity = (new CaseEntity())
            ->setSubject('Test Case')
            ->setDescription('Test case description')
            ->setResolution('Test resolution')
            ->setOrganization($organization)
            ->setOwner($owner);
        $this->persistTestEntity($caseEntity);

        $lead = (new Lead())
            ->setName('Test Lead')
            ->setFirstName('Bob')
            ->setMiddleName('R')
            ->setLastName('Johnson')
            ->setNamePrefix('Dr.')
            ->setNameSuffix('PhD')
            ->setCompanyName('Test Company')
            ->setOrganization($organization)
            ->setOwner($owner);
        $this->persistTestEntity($lead);

        $customer = new Customer();
        $customer->setAccount($account);
        $manager->persist($customer);

        $opportunity = (new Opportunity())
            ->setName('Test Opportunity')
            ->setContact($contact)
            ->setCustomerAssociation($customer)
            ->setOrganization($organization)
            ->setOwner($owner);
        $this->persistTestEntity($opportunity);

        $b2bCustomer = (new B2bCustomer())
            ->setName('Test B2B Customer')
            ->setContact($contact)
            ->setOrganization($organization)
            ->setOwner($owner);
        $b2bCustomer->setDataChannel($channel);
        $this->persistTestEntity($b2bCustomer);

        $caseMailboxProcessSettings = (new CaseMailboxProcessSettings())->setOwner($owner);
        $this->persistTestEntity($caseMailboxProcessSettings);

        $manager->flush();
    }
}
