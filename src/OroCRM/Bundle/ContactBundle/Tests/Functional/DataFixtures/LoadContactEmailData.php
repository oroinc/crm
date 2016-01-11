<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;

class LoadContactEmailData extends AbstractFixture implements DependentFixtureInterface
{
    const FIRST_ENTITY_NAME  = 'test1@test.test';
    const SECOND_ENTITY_NAME = 'test2@test.test';
    const THIRD_ENTITY_NAME  = 'test3@test.test';

    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData'
        ];
    }

    /**
     * @var array
     */
    protected $contactEmailData = [
        [
            'email' => self::FIRST_ENTITY_NAME,
            'primary'  => true,
        ],
        [
            'email' => self::SECOND_ENTITY_NAME,
            'primary'  => false,
        ],
        [
            'email' => self::THIRD_ENTITY_NAME,
            'primary'  => false,
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $contact = $this->getReference('Contact_' . LoadContactEntitiesData::FIRST_ENTITY_NAME);

        foreach ($this->contactEmailData as $contactEmailData) {
            $contactEmail = new ContactEmail();
            $contactEmail->setPrimary($contactEmailData['primary']);
            $contactEmail->setOwner($contact);
            $contactEmail->setEmail($contactEmailData['email']);
            $this->setReference('ContactEmail_Several_' . $contactEmailData['email'], $contactEmail);
            $manager->persist($contactEmail);
        }

        $contact2 = $this->getReference('Contact_' . LoadContactEntitiesData::SECOND_ENTITY_NAME);
        $contactEmail = new ContactEmail();
        $contactEmail->setPrimary($this->contactEmailData[0]['primary']);
        $contactEmail->setOwner($contact2);
        $contactEmail->setEmail($this->contactEmailData[0]['email']);
        $this->setReference('ContactEmail_Single_' . $this->contactEmailData[0]['email'], $contactEmail);
        $manager->persist($contactEmail);


        $manager->flush();
    }
}
