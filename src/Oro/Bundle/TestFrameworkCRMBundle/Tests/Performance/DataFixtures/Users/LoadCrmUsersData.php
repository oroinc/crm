<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Tests\Performance\DataFixtures\Users;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCrmUsersData extends AbstractFixture implements ContainerAwareInterface
{
    const USERS_NUMBER  = 200;

    /** @var EntityManagerInterface */
    private $em;

    /** @var UserManager */
    private $userManager;

    /** @var array|null */
    private $firstNamesDictionary;

    /** @var array|null */
    private $lastNamesDictionary;

    /** @var Role */
    private $role;

    /** @var BusinessUnit */
    private $businessUnit;

    /** @var Organization */
    private $organization;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->userManager = $container->get('oro_user.manager');
        $this->em = $container->get('doctrine')->getManager();
        $this->role = $this->em->getRepository('OroUserBundle:Role')
            ->findBy(array('role' => 'ROLE_ADMINISTRATOR'));
        $this->businessUnit = $this->em->getRepository('OroOrganizationBundle:BusinessUnit')->getFirst();
        $this->organization = $this->em->getRepository('OroOrganizationBundle:Organization')->getFirst();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadUsers();
        $manager->flush();
    }

    /**
     * Load users
     *
     * @return void
     */
    public function loadUsers()
    {
        for ($i = 0; $i < self::USERS_NUMBER; ++$i) {
            $firstName  = $this->generateFirstName();
            $lastName   = $this->generateLastName();
            $middleName = $this->generateMiddleName();
            $birthday   = $this->generateBirthday();
            $username   = $this->generateUsername($firstName, $lastName);
            $email      = $this->generateEmail($firstName, $lastName);

            $user = $this->createUser(
                $username,
                $email,
                $firstName,
                $lastName,
                $middleName,
                $birthday,
                $this->organization
            );

            $user->setPlainPassword($username);
            $this->userManager->updatePassword($user);
            $this->userManager->updateUser($user);
        }
    }

    /**
     * Creates a user
     *
     * @param string $username
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $middleName
     * @param \DateTime $birthday
     * @param Organization $organization
     * @return User
     */
    private function createUser(
        $username,
        $email,
        $firstName,
        $lastName,
        $middleName,
        $birthday,
        $organization
    ) {
        /** @var User $user */
        $user = $this->userManager->createUser();

        $user->setEmail($email);
        $user->setUsername($username);
        $user->setFirstName($firstName);
        $user->setMiddlename($middleName);
        $user->setLastName($lastName);
        $user->setBirthday($birthday);
        $user->addUserRole($this->role[0]);
        $user->setBusinessUnits(new ArrayCollection([$this->businessUnit]));
        $user->setOwner($this->businessUnit);
        $user->setOrganization($organization);
        $user->addOrganization($organization);
        return $user;
    }

    /**
     * Generates a username
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    private function generateUsername($firstName, $lastName)
    {
        return sprintf("%s.%s", strtolower($firstName), strtolower($lastName));
    }

    /**
     * Generates an email
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    private function generateEmail($firstName, $lastName)
    {
        $domains     = array('yahoo.com', 'gmail.com', 'example.com', 'hotmail.com', 'aol.com', 'msn.com');
        $randomIndex = rand(0, count($domains) - 1);
        $domain      = $domains[$randomIndex];

        return sprintf("%s.%s@%s", strtolower($firstName), strtolower($lastName), $domain);
    }

    /**
     * Generate a first name
     *
     * @return string
     */
    private function generateFirstName()
    {
        if (is_null($this->firstNamesDictionary)) {
            $this->firstNamesDictionary = $this->loadDictionary('first_names.txt');
            shuffle($this->firstNamesDictionary);
        }

        $randomIndex = rand(0, count($this->firstNamesDictionary) - 1);
        $randomName  = $this->firstNamesDictionary[$randomIndex];
        unset($this->firstNamesDictionary[$randomIndex]);
        if (!is_null($this->firstNamesDictionary)) {
            $this->firstNamesDictionary = array_values($this->firstNamesDictionary);
        }

        return trim($randomName);
    }

    /**
     * Generate a middle name
     *
     * @return string
     */
    private function generateMiddleName()
    {
        return '';
    }

    /**
     * Loads dictionary from file by name
     *
     * @param string $name
     * @return array
     */
    private function loadDictionary($name)
    {
        static $dictionaries = array();

        if (!isset($dictionaries[$name])) {
            $dictionary = array();
            $fileName   = __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
            foreach (file($fileName) as $item) {
                $dictionary[] = trim($item);
            }
            $dictionaries[$name] = $dictionary;
        }

        return $dictionaries[$name];
    }

    /**
     * Generates a last name
     *
     * @return string
     */
    private function generateLastName()
    {
        if (is_null($this->lastNamesDictionary)) {
            $this->lastNamesDictionary = $this->loadDictionary('last_names.txt');
            shuffle($this->lastNamesDictionary);
        }

        $randomIndex = rand(0, count($this->lastNamesDictionary) - 1);
        $randomName  = $this->lastNamesDictionary[$randomIndex];
        unset($this->lastNamesDictionary[$randomIndex]);
        if (!is_null($this->lastNamesDictionary)) {
            $this->lastNamesDictionary = array_values($this->lastNamesDictionary);
        }

        return trim($randomName);
    }

    /**
     * Generates a date of birth
     *
     * @return \DateTime
     */
    private function generateBirthday()
    {
        // Convert to timetamps
        $min = strtotime('1950-01-01');
        $max = strtotime('2000-01-01');

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return new \DateTime(date('Y-m-d', $val), new \DateTimeZone('UTC'));
    }
}
