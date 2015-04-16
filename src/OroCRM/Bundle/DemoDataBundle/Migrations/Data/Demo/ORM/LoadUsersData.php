<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoadUsersData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const FLUSH_MAX = 10;

    /** @var ContainerInterface */
    protected $container;

    /** @var UserManager */
    protected $userManager;

    /** @var Role */
    protected $role;

    /** @var  EntityManager */
    protected $em;

    /**
     * @var Organization
     */
    protected $organization;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadBusinessUnitData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUserData',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }
        $this->organization = $this->getReference('default_organization');
        $this->userManager  = $this->container->get('oro_user.manager');
        $this->role = $this->em->getRepository('OroUserBundle:Role')->findOneBy(
            array('role' => LoadRolesData::ROLE_MANAGER)
        );
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadUsers();
    }

    /**
     * Load users
     *
     * @return void
     */
    public function loadUsers()
    {
        for ($i = 0; $i < 50; ++$i) {
            $firstName = $this->generateFirstName();
            $lastName = $this->generateLastName();
            $birthday = $this->generateBirthday();
            $username = $this->generateUsername($firstName, $lastName);
            $email = $this->generateEmail($firstName, $lastName);

            $user = $this->createUser(
                $username,
                $email,
                $firstName,
                $lastName,
                $birthday,
                $this->role
            );

            $user->setPlainPassword($username);
            $this->userManager->updatePassword($user);

            $this->persist($user);
            if ($i % self::FLUSH_MAX == 0) {
                $this->flush();
                $this->em->clear();
                $this->initSupportingEntities();
            }
        }
        $this->flush();
    }

    /**
     * Creates a user
     *
     * @param  string    $username
     * @param  string    $email
     * @param  string    $firstName
     * @param  string    $lastName
     * @param  \DateTime $birthday
     * @param  mixed     $role
     * @return User
     */
    private function createUser($username, $email, $firstName, $lastName, $birthday, $role)
    {
        /** @var $user User */
        $user = $this->userManager->createUser();

        $user->setEmail($email);
        $user->setUsername($username);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setBirthday($birthday);
        $user->setOwner($this->getBusinessUnit('Acme, General'));
        $user->addBusinessUnit($this->getBusinessUnit('Acme, General'));
        $user->addRole($role);
        $user->setOrganization($this->organization);
        $user->addOrganization($this->organization);

        return $user;
    }

    /**
     * Generates a username
     *
     * @param  string $firstName
     * @param  string $lastName
     * @return string
     */
    private function generateUsername($firstName, $lastName)
    {
        $uniqueString = substr(uniqid(rand()), -5, 5);

        return sprintf("%s.%s_%s", strtolower($firstName), strtolower($lastName), $uniqueString);
    }

    /**
     * Generates an email
     *
     * @param  string $firstName
     * @param  string $lastName
     * @return string
     */
    private function generateEmail($firstName, $lastName)
    {
        $uniqueString = substr(uniqid(rand()), -5, 5);
        $domains = array('yahoo.com', 'gmail.com', 'example.com', 'hotmail.com', 'aol.com', 'msn.com');
        $randomIndex = rand(0, count($domains) - 1);
        $domain = $domains[$randomIndex];

        return sprintf("%s.%s_%s@%s", strtolower($firstName), strtolower($lastName), $uniqueString, $domain);
    }

    /**
     * Generate a first name
     *
     * @return string
     */
    private function generateFirstName()
    {
        $firstNamesDictionary = $this->loadDictionary('first_names.txt');
        $randomIndex = rand(0, count($firstNamesDictionary) - 1);

        return trim($firstNamesDictionary[$randomIndex]);
    }

    /**
     * Loads dictionary from file by name
     *
     * @param  string $name
     * @return array
     */
    private function loadDictionary($name)
    {
        static $dictionaries = array();

        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroCRMDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        if (!isset($dictionaries[$name])) {
            $dictionary = array();
            $fileName = $dictionaryDir . DIRECTORY_SEPARATOR . $name;
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
        $lastNamesDictionary = $this->loadDictionary('last_names.txt');
        $randomIndex = rand(0, count($lastNamesDictionary) - 1);

        return trim($lastNamesDictionary[$randomIndex]);
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

    /**
     * Persist object
     *
     * @param  mixed $object
     * @return void
     */
    private function persist($object)
    {
        $this->em->persist($object);
    }

    /**
     * Flush objects
     *
     * @return void
     */
    private function flush()
    {
        $this->em->flush();
    }

    /**
     * @param $name
     * @return BusinessUnit
     */
    protected function getBusinessUnit($name)
    {
        return $this->em->getRepository('OroOrganizationBundle:BusinessUnit')->findOneBy(['name' => $name]);
    }
}
