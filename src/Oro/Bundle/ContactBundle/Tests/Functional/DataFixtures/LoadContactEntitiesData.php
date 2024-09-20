<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\Entity\User;

class LoadContactEntitiesData extends AbstractFixture implements DependentFixtureInterface
{
    public const FIRST_ENTITY_NAME  = 'Brenda';
    public const SECOND_ENTITY_NAME = 'Richard';
    public const THIRD_ENTITY_NAME  = 'Shawn';
    public const FOURTH_ENTITY_NAME = 'Faye';

    public static $owner = 'admin';

    private array $contactsData = [
        [
            'firstName' => self::FIRST_ENTITY_NAME,
            'lastName'  => 'Bradley',
            'testMultiEnum' => 'bob_marley'
        ],
        [
            'firstName' => self::SECOND_ENTITY_NAME,
            'lastName'  => 'Brock',
            'TelephoneNumber' => '585-255-1127'
        ],
        [
            'firstName' => self::THIRD_ENTITY_NAME,
            'lastName'  => 'Bryson'
        ],
        [
            'firstName' => self::FOURTH_ENTITY_NAME,
            'lastName'  => 'Church'
        ],
    ];

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->findOneByUsername(self::$owner);
        foreach ($this->contactsData as $contactData) {
            $contact = new Contact();
            $contact->setOwner($user);
            $contact->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
            $contact->setFirstName($contactData['firstName']);
            $contact->setLastName($contactData['lastName']);

            if (isset($contactData['TelephoneNumber'])) {
                $phone = new ContactPhone($contactData['TelephoneNumber']);
                $phone->setPrimary(true);
                $contact->addPhone($phone);
            }

            if (isset($contactData['testMultiEnum'])) {
                $multiEnumValue = $manager->getRepository(EnumOption::class)
                    ->find(ExtendHelper::buildEnumOptionId('test_multi_enum', $contactData['testMultiEnum']));
                $contact->setTestMultiEnum(new ArrayCollection([$multiEnumValue]));
            }

            $this->setReference('Contact_' . $contactData['firstName'], $contact);
            $manager->persist($contact);
        }
        $manager->flush();
    }
}
