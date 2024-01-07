<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;

class LoadContactData extends AbstractFixture implements DependentFixtureInterface
{
    private array $contactsData = [
        'oro_case_contact' => [
            'firstName' => 'Daniel',
            'lastName'  => 'Case',
            'email'     => 'daniel.case@example.com'
        ]
    ];

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganization::class, LoadUser::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->contactsData as $reference => $contactData) {
            $contact = new Contact();
            $contact->setOwner($this->getReference(LoadUser::USER));
            $contact->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
            $contact->setFirstName($contactData['firstName']);
            $contact->setLastName($contactData['lastName']);
            $contact->setEmail($contactData['email']);
            $manager->persist($contact);
            $this->setReference($reference, $contact);
        }
        $manager->flush();
    }
}
