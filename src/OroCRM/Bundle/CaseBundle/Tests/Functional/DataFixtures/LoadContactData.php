<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

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
            'reference' => 'orocrm_case_contact'
        )
    );

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $adminUser = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

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
