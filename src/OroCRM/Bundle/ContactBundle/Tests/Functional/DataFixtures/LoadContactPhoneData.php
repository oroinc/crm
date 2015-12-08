<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

class LoadContactPhoneData extends AbstractFixture implements DependentFixtureInterface
{
    const FIRST_ENTITY_NAME  = '1111111';
    const SECOND_ENTITY_NAME = '2222222';
    const THIRD_ENTITY_NAME  = '3333333';

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
            'phone' => self::FIRST_ENTITY_NAME,
            'primary'  => true,
        ],
        [
            'phone' => self::SECOND_ENTITY_NAME,
            'primary'  => false,
        ],
        [
            'phone' => self::THIRD_ENTITY_NAME,
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
            $contactPhone = new ContactPhone();
            $contactPhone->setPrimary($contactEmailData['primary']);
            $contactPhone->setOwner($contact);
            $contactPhone->setPhone($contactEmailData['phone']);

            $this->setReference('ContactPhone_Several_' . $contactEmailData['phone'], $contactPhone);
            $manager->persist($contactPhone);
        }

        $contact2 = $this->getReference('Contact_' . LoadContactEntitiesData::SECOND_ENTITY_NAME);
        $contactPhone = new ContactPhone();
        $contactPhone->setPrimary($this->contactEmailData[0]['primary']);
        $contactPhone->setOwner($contact2);
        $contactPhone->setPhone($this->contactEmailData[0]['phone']);
        $this->setReference('ContactPhone_Single_' . $this->contactEmailData[0]['phone'], $contactPhone);
        $manager->persist($contactPhone);

        $manager->flush();
    }
}
