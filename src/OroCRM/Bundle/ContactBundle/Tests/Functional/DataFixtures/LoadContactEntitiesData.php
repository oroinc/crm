<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class LoadContactEntitiesData extends AbstractFixture
{
    const FIRST_ENTITY_NAME  = 'Brenda';
    const SECOND_ENTITY_NAME = 'Richard';
    const THIRD_ENTITY_NAME  = 'Shawn';
    const FOURTH_ENTITY_NAME = 'Faye';

    public static $owner = 'admin';

    /**
     * @var array
     */
    protected $contactsData = [
        [
            'firstName' => self::FIRST_ENTITY_NAME,
            'lastName'  => 'Bradley',
        ],
        [
            'firstName' => self::SECOND_ENTITY_NAME,
            'lastName'  => 'Brock',
        ],
        [
            'firstName' => self::THIRD_ENTITY_NAME,
            'lastName'  => 'Bryson',
        ],
        [
            'firstName' => self::FOURTH_ENTITY_NAME,
            'lastName'  => 'Church',
        ],

    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('OroUserBundle:User')->findOneByUsername(self::$owner);
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->contactsData as $contactData) {
            $contact = new Contact();
            $contact->setOwner($user);
            $contact->setOrganization($organization);
            $contact->setFirstName($contactData['firstName']);
            $contact->setLastName($contactData['lastName']);
            $this->setReference('Contact_' . $contactData['firstName'], $contact);
            $manager->persist($contact);
        }

        $manager->flush();
    }
}
