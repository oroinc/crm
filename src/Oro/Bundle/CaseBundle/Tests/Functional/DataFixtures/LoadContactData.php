<?php

namespace Oro\Bundle\CaseBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

class LoadContactData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $contactsData = array(
        array(
            'firstName' => 'Daniel',
            'lastName'  => 'Case',
            'email'     => 'daniel.case@example.com',
            'reference' => 'oro_case_contact'
        )
    );

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $adminUser = $manager->getRepository(User::class)->findOneByUsername('admin');
        $organization = $manager->getRepository(Organization::class)->getFirst();

        foreach ($this->contactsData as $contactData) {
            $contact = new Contact();
            $contact->setOwner($adminUser);
            $contact->setOrganization($organization);
            $contact->setFirstName($contactData['firstName']);
            $contact->setLastName($contactData['lastName']);
            $contact->setEmail($contactData['email']);

            $manager->persist($contact);

            $this->setReference($contactData['reference'], $contact);
        }

        $manager->flush();
    }
}
