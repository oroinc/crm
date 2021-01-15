<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;

class LoadContactPhoneData extends AbstractFixture implements DependentFixtureInterface
{
    const FIRST_ENTITY_NAME  = '1111111';
    const SECOND_ENTITY_NAME = '2222222';
    const THIRD_ENTITY_NAME  = '3333333';
    const FOURTH_ENTITY_NAME  = '4444444';

    public function getDependencies()
    {
        return [
            'Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData'
        ];
    }

    /**
     * @var array
     */
    protected $contactPhoneData = [
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
        foreach ($this->contactPhoneData as $contactPhoneData) {
            $contactPhone = new ContactPhone();
            $contactPhone->setPrimary($contactPhoneData['primary']);
            $contactPhone->setPhone($contactPhoneData['phone']);
            $contact->addPhone($contactPhone);

            $this->setReference('ContactPhone_Several_' . $contactPhoneData['phone'], $contactPhone);
            $manager->persist($contactPhone);
        }

        $contact2 = $this->getReference('Contact_' . LoadContactEntitiesData::SECOND_ENTITY_NAME);
        $contactPhone = new ContactPhone();
        $contactPhone->setPrimary($this->contactPhoneData[0]['primary']);
        $contactPhone->setPhone($this->contactPhoneData[0]['phone']);
        $contact2->addPhone($contactPhone);
        $this->setReference('ContactPhone_Single_' . $this->contactPhoneData[0]['phone'], $contactPhone);
        $manager->persist($contactPhone);

        $contact3 = $this->getReference('Contact_' . LoadContactEntitiesData::FOURTH_ENTITY_NAME);
        $contactPhone = new ContactPhone();
        $contactPhone->setPrimary(true);
        $contactPhone->setPhone(self::FOURTH_ENTITY_NAME);
        $contact3->addPhone($contactPhone);
        $this->setReference('ContactPhone_Single_' . self::FOURTH_ENTITY_NAME, $contactPhone);
        $manager->persist($contactPhone);

        $manager->flush();
    }
}
