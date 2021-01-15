<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

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
            'testMultiEnum' => 'bob_marley'
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

            if (isset($contactData['testMultiEnum'])) {
                $testMultiEnumValue = $manager->getRepository(
                    ExtendHelper::buildEnumValueClassName('test_multi_enum')
                )->find($contactData['testMultiEnum']);

                $contact->setTestMultiEnum(
                    new ArrayCollection(
                        [
                            $testMultiEnumValue,
                        ]
                    )
                );
            }

            $this->setReference('Contact_' . $contactData['firstName'], $contact);
            $manager->persist($contact);
        }

        $manager->flush();
    }
}
