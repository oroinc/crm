<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use OroCRM\Bundle\DemoDataBundle\DataFixtures\AbstractFlexibleFixture;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoadUsersData extends AbstractFlexibleFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var FlexibleEntityRepository
     */
    protected $userRepository;

    /** @var  TagManager */
    protected $tagManager;

    /** @var FlexibleEntityRepository */
    protected $roles;

    /** @var EntityRepository */
    protected $tags;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->userManager = $container->get('oro_user.manager');
        $this->userRepository = $this->userManager->getFlexibleRepository();
        $this->tagManager = $container->get('oro_tag.tag.manager');

        $entityManager = $container->get('doctrine.orm.entity_manager');
        $this->roles = $entityManager->getRepository('OroUserBundle:Role');
        $this->tags = $entityManager->getRepository('OroTagBundle:Tag');
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(ObjectManager $manager)
    {
        $this->loadAttributes();
        $this->loadUsers();
    }

    /**
     * Load attributes
     *
     * @return void
     */
    public function loadAttributes()
    {
        $this->assertHasRequiredAttributes($this->userManager, array('company', 'gender'));

        if (!$this->findAttribute($this->userManager, 'website')) {
            $websiteAttribute = $this->createAttribute($this->userManager, 'oro_flexibleentity_url', 'website');
            $this->persist($websiteAttribute);
        }

        if (!$this->findAttribute($this->userManager, 'hobby')) {
            $hobbyAttribute = $this->createAttributeWithOptions(
                $this->userManager,
                'oro_flexibleentity_multiselect',
                'hobby',
                self::getHobbies()
            );
            $this->persist($hobbyAttribute);
        }

        $this->flush();
    }

    /**
     * Asserts required attributes were created
     *
     * @param EntityManager $entityManager
     * @param string $attributeCodes
     * @throws \LogicException
     */
    private function assertHasRequiredAttributes($entityManager, $attributeCodes)
    {
        foreach ($attributeCodes as $attributeCode) {
            if (!$this->findAttribute($entityManager, $attributeCode)) {
                throw new \LogicException(
                    sprintf(
                        'Attribute "%s" is missing, please load "%s" fixture before',
                        $attributeCode,
                        'Acme\Bundle\DemoBundle\DataFixtures\ORM\LoadUserAttrData'
                    )
                );
            }
        }
    }

    /**
     * Load users
     *
     * @return void
     */
    public function loadUsers()
    {
        /** @var \Oro\Bundle\UserBundle\Entity\Role $role */
        $role = $this->roles->findOneBy(array('role' => 'ROLE_MANAGER'));

        for ($i = 0; $i < 50; ++$i) {
            $firstName = $this->generateFirstName();
            $lastName = $this->generateLastName();
            $birthday = $this->generateBirthday();
            $username = $this->generateUsername($firstName, $lastName);
            $email = $this->generateEmail($firstName, $lastName);
            $company = $this->generateCompany();
            $website = $this->generateWebsite($firstName, $lastName);
            $gender = $this->generateGender();
            $hobbies = $this->generateHobbies();

            $user = $this->createUser(
                $username,
                $email,
                $firstName,
                $lastName,
                $birthday,
                $company,
                $website,
                $gender,
                $hobbies,
                $role
            );

            $user->setPlainPassword($username);
            $this->userManager->updatePassword($user);

            $this->persist($user);

        }
        $this->flush();
    }

    /**
     * Creates a user
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     *
     * @param string $username
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param \DateTime $birthday
     * @param string $company
     * @param string $website
     * @param string $gender
     * @param array $hobbies
     * @param mixed $role
     * @return User
     */
    private function createUser(
        $username,
        $email,
        $firstName,
        $lastName,
        $birthday,
        $company,
        $website,
        $gender,
        array $hobbies,
        $role
    ) {
        /** @var $user User */
        $user = $this->userManager->createFlexible();

        $user->setEmail($email);
        $user->setUsername($username);
        $user->setFirstname($firstName);
        //$user->setMiddlename($middleName);
        $user->setLastname($lastName);
        $user->setBirthday($birthday);
        $user->setOwner($this->getReference('default_main_business'));
        $this->setFlexibleAttributeValue($this->userManager, $user, 'company', $company);
        $this->setFlexibleAttributeValueOption($this->userManager, $user, 'gender', $gender);
        $this->setFlexibleAttributeValue($this->userManager, $user, 'website', $website);
        $this->addFlexibleAttributeValueOptions($this->userManager, $user, 'hobby', $hobbies);
        $user->addRole($role);
        return $user;
    }
    /**
     * Create an attribute with options
     *
     * @param EntityManager $entityManager
     * @param string $attributeType
     * @param string $attributeCode
     * @param array $optionValues
     * @return AbstractAttribute
     */
    private function createAttributeWithOptions(
        $entityManager,
        $attributeType,
        $attributeCode,
        array $optionValues
    ) {
        $attribute = $this->createAttribute($entityManager, $attributeType, $attributeCode);
        foreach ($optionValues as $value) {
            $attribute->addOption($this->createAttributeOptionWithValue($entityManager, $value));
        }
        return $attribute;
    }

    /**
     * Create an attribute option with value
     *
     * @param EntityManager $entityManager
     * @param string $value
     * @return AbstractAttributeOption
     */
    private function createAttributeOptionWithValue($entityManager, $value)
    {
        $option = $entityManager->createAttributeOption();
        $optionValue = $entityManager->createAttributeOptionValue()->setValue($value);
        $option->addOptionValue($optionValue);
        return $option;
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
        $uniqueString = substr(uniqid(rand()), -5, 5);
        return sprintf("%s.%s_%s", strtolower($firstName), strtolower($lastName), $uniqueString);
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
     * @param string $name
     * @return array
     */
    private function loadDictionary($name)
    {
        static $dictionaries = array();

        if (!isset($dictionaries[$name])) {
            $dictionary = array();
            $fileName = __DIR__ . DIRECTORY_SEPARATOR . 'dictionaries' . DIRECTORY_SEPARATOR . $name;
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
     * Generates a company name
     *
     * @return string
     */
    private function generateCompany()
    {
        $companyNames = $this->loadDictionary('company_names.txt');
        $randomIndex = rand(0, count($companyNames) - 1);

        return trim($companyNames[$randomIndex]);
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
     * Generates a website
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    private function generateWebsite($firstName, $lastName)
    {
        $domain = 'example.com';
        return sprintf("http://%s%s.%s", strtolower($firstName), strtolower($lastName), $domain);
    }

    /**
     * Generates a gender
     *
     * @return string
     */
    private function generateGender()
    {
        $genders = array('Male', 'Female');
        return $genders[rand(0, 1)];
    }

    /**
     * Generates hobbies
     *
     * @return string
     */
    private function generateHobbies()
    {
        $hobbies = self::getHobbies();
        $randomCount = rand(1, count($hobbies));
        shuffle($hobbies);
        return array_slice($hobbies, 0, $randomCount);
    }

    /**
     * Get array of hobbies
     *
     * @return array
     */
    private static function getHobbies()
    {
        return array('Sport', 'Cooking', 'Read', 'Coding!');
    }

    /**
     * Persist object
     *
     * @param mixed $object
     * @return void
     */
    private function persist($object)
    {
        $this->userManager->getStorageManager()->persist($object);
    }

    /**
     * Flush objects
     *
     * @return void
     */
    private function flush()
    {
        $this->userManager->getStorageManager()->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 130;
    }
}
