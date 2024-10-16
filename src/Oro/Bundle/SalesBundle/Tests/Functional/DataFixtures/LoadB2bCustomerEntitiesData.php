<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\Entity\User;

class LoadB2bCustomerEntitiesData extends AbstractFixture implements DependentFixtureInterface
{
    public const FIRST_ENTITY_NAME  = 'Life Plan Councelling';
    public const SECOND_ENTITY_NAME = 'Big D Supermarkets';
    public const THIRD_ENTITY_NAME  = 'Cherry Webb';
    public const FOURTH_ENTITY_NAME = 'National Lumber';

    public static $owner = 'admin';

    private array $b2bCustomersData = [
        self::FIRST_ENTITY_NAME,
        self::SECOND_ENTITY_NAME,
        self::THIRD_ENTITY_NAME,
        self::FOURTH_ENTITY_NAME
    ];

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->findOneByUsername(self::$owner);
        foreach ($this->b2bCustomersData as $customerName) {
            $contact = new B2bCustomer();
            $contact->setOwner($user);
            $contact->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
            $contact->setName($customerName);
            $this->setReference('B2bCustomer_' . $customerName, $contact);
            $manager->persist($contact);
        }
        $manager->flush();
    }
}
