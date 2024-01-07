<?php

declare(strict_types=1);

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loads users into the default_organization and assigns them to ROLE_MANAGER.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoadUsersData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    private const FLUSH_MAX = 10;

    private Organization $organization;
    private BusinessUnit $businessUnit;
    private Role $role;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            LoadBusinessUnitData::class,
            LoadUserData::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $userManager = $this->container->get('oro_user.manager');
        $this->initSupportingEntities($manager);
        for ($i = 0; $i < 50; ++$i) {
            $firstName = $this->generateFirstName();
            $lastName = $this->generateLastName();
            $birthday = $this->generateBirthday();
            $username = $this->generateUsername($firstName, $lastName);
            $email = $this->generateEmail($firstName, $lastName);

            $user = $this->createUser($userManager, $username, $email, $firstName, $lastName, $birthday);
            $user->setPlainPassword($username);
            $userManager->updatePassword($user);
            $userManager->updateUser($user);

            if (0 === $i % self::FLUSH_MAX) {
                $manager->flush();
                $manager->clear();
                $this->initSupportingEntities($manager);
            }
        }
        $manager->flush();
    }

    private function initSupportingEntities(ObjectManager $manager): void
    {
        $this->organization = $this->getReference('default_organization');
        $this->businessUnit = $manager->getRepository(BusinessUnit::class)->findOneBy(['name' => 'Acme, General']);
        $this->role = $manager->getRepository(Role::class)->findOneBy(['role' => LoadRolesData::ROLE_MANAGER]);
    }

    private function createUser(
        UserManager $userManager,
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        \DateTime $birthday
    ): User {
        /** @var User $user */
        $user = $userManager->createUser();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setBirthday($birthday);
        $user->setOwner($this->businessUnit);
        $user->addBusinessUnit($this->businessUnit);
        $user->addUserRole($this->role);
        $user->setOrganization($this->organization);
        $user->addOrganization($this->organization);

        return $user;
    }

    private function generateUsername(string $firstName, string $lastName): string
    {
        $uniqueString = \substr(\uniqid(\strval(\rand()), false), -5, 5);

        return \sprintf('%s.%s_%s', \strtolower($firstName), \strtolower($lastName), $uniqueString);
    }

    private function generateEmail(string $firstName, string $lastName): string
    {
        $uniqueString = \substr(\uniqid(\strval(\rand()), false), -5, 5);
        $domains = ['yahoo.com', 'gmail.com', 'example.com', 'hotmail.com', 'aol.com', 'msn.com'];
        $randomIndex = \rand(0, \count($domains) - 1);
        $domain = $domains[$randomIndex];

        return \sprintf('%s.%s_%s@%s', \strtolower($firstName), \strtolower($lastName), $uniqueString, $domain);
    }

    private function generateFirstName(): string
    {
        $firstNamesDictionary = $this->loadDictionaryFromFile('first_names.txt');
        $randomIndex = \rand(0, \count($firstNamesDictionary) - 1);

        return \trim($firstNamesDictionary[$randomIndex]);
    }

    private function loadDictionaryFromFile(string $fileName): array
    {
        static $dictionaries = [];

        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        if (!isset($dictionaries[$fileName])) {
            $dictionary = [];
            $filePath = $dictionaryDir . DIRECTORY_SEPARATOR . $fileName;
            foreach (file($filePath) as $item) {
                $dictionary[] = trim($item);
            }
            $dictionaries[$fileName] = $dictionary;
        }

        return $dictionaries[$fileName];
    }

    private function generateLastName(): string
    {
        $lastNamesDictionary = $this->loadDictionaryFromFile('last_names.txt');
        $randomIndex = \rand(0, \count($lastNamesDictionary) - 1);

        return \trim($lastNamesDictionary[$randomIndex]);
    }

    private function generateBirthday(): \DateTime
    {
        // Convert to timestamps
        $min = \strtotime('1950-01-01');
        $max = \strtotime('2000-01-01');

        // Generate random number using above bounds
        $val = \rand($min, $max);

        // Convert back to desired date format
        return new \DateTime(date('Y-m-d', $val), new \DateTimeZone('UTC'));
    }
}
